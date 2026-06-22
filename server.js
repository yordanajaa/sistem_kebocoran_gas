require('dotenv').config();
const express = require('express');
const cors = require('cors');
const mysql = require('mysql2/promise');
const bcrypt = require('bcrypt');
const jwt = require('jsonwebtoken');
const nodemailer = require('nodemailer');
const mqtt = require('mqtt');

const app = express();
app.use(cors());
app.use(express.json());

// Root Route for Browser check
app.get('/', (req, res) => {
    res.json({ message: 'IoT Gas Leak API is running successfully!' });
});

// --- Database Connection ---
const db = mysql.createPool({
    host: process.env.MYSQLHOST || process.env.DB_HOST || 'localhost',
    user: process.env.MYSQLUSER || process.env.DB_USER || 'root',
    password: process.env.MYSQLPASSWORD || process.env.DB_PASS || '',
    database: process.env.MYSQLDATABASE || process.env.DB_NAME || 'otp_system',
    port: process.env.MYSQLPORT || 3306
});

// --- MQTT Connection ---
let sensorData = { gas: 0, api: 0 };
let sensorReports = [];

const mqttClient = mqtt.connect(process.env.MQTT_SERVER || 'mqtt://broker.hivemq.com', {
    username: process.env.MQTT_USER || '',
    password: process.env.MQTT_PASS || '',
    rejectUnauthorized: false // like setInsecure() in ESP32
});

mqttClient.on('connect', () => {
    console.log('Connected to HiveMQ');
    mqttClient.subscribe('chaos/sensor/data');
    mqttClient.subscribe('chaos/report');
});

mqttClient.on('message', (topic, message) => {
    const msgStr = message.toString();
    if (topic === 'chaos/sensor/data') {
        try {
            sensorData = JSON.parse(msgStr);
        } catch (e) {
            console.error('Failed to parse sensor data', e);
        }
    } else if (topic === 'chaos/report') {
        if (!sensorReports.includes(msgStr)) {
            sensorReports.unshift(msgStr);
            if (sensorReports.length > 50) sensorReports.pop(); // Keep last 50
        }
    }
});

// --- Nodemailer Setup ---
const transporter = nodemailer.createTransport({
    service: 'gmail',
    auth: {
        user: process.env.GMAIL_USER,
        pass: process.env.GMAIL_APP_PASSWORD
    }
});

// --- JWT Middleware ---
const authenticateToken = (req, res, next) => {
    const authHeader = req.headers['authorization'];
    const token = authHeader && authHeader.split(' ')[1];
    if (!token) return res.status(401).json({ success: false, message: 'Access denied' });

    jwt.verify(token, process.env.JWT_SECRET, (err, user) => {
        if (err) return res.status(403).json({ success: false, message: 'Invalid token' });
        req.user = user;
        next();
    });
};

// --- AUTH API ENDPOINTS ---

// Register
app.post('/api/auth/register', async (req, res) => {
    const { name, email, password } = req.body;
    if (!name || !email || !password) return res.json({ success: false, message: 'All fields required' });

    try {
        const [existing] = await db.query('SELECT id FROM users WHERE email = ?', [email]);
        if (existing.length > 0) return res.json({ success: false, message: 'Email already registered' });

        const hashedPassword = await bcrypt.hash(password, 10);
        await db.query('INSERT INTO users (name, email, password, is_verified) VALUES (?, ?, ?, 1)', [name, email, hashedPassword]);
        res.json({ success: true, message: 'Registration successful' });
    } catch (err) {
        res.json({ success: false, message: err.message });
    }
});

// Login
app.post('/api/auth/login', async (req, res) => {
    const { email, password } = req.body;
    if (!email || !password) return res.json({ success: false, message: 'Email and password required' });

    try {
        const [rows] = await db.query('SELECT * FROM users WHERE email = ?', [email]);
        const user = rows[0];

        if (!user || !(await bcrypt.compare(password, user.password))) {
            return res.json({ success: false, message: 'Invalid email or password' });
        }

        await db.query('UPDATE users SET last_login = NOW() WHERE id = ?', [user.id]);

        // Generate JWT
        const token = jwt.sign({ id: user.id, email: user.email }, process.env.JWT_SECRET || 'fallback_secret_key_123', { expiresIn: '1d' });
        res.json({ success: true, message: 'Login successful', token, email: user.email });
    } catch (err) {
        res.json({ success: false, message: err.message });
    }
});

// Send OTP
app.post('/api/auth/send-otp', async (req, res) => {
    const { email } = req.body;
    if (!email) return res.json({ success: false, message: 'Email required' });

    try {
        const otp = Math.floor(100000 + Math.random() * 900000).toString();
        const expiresAt = new Date(Date.now() + 5 * 60000); // 5 minutes

        // Inactivate old OTPs
        await db.query('UPDATE otp_codes SET is_used = 1 WHERE email = ? AND is_used = 0', [email]);
        
        // Insert new OTP
        await db.query('INSERT INTO otp_codes (email, otp_code, expires_at) VALUES (?, ?, ?)', [email, otp, expiresAt]);

        // Send Email
        const mailOptions = {
            from: `"OTP System" <${process.env.GMAIL_USER}>`,
            to: email,
            subject: 'Your Password Reset OTP',
            html: `<h3>Your Verification Code</h3><p>Your code is: <b>${otp}</b></p><p>Valid for 5 minutes.</p>`
        };

        await transporter.sendMail(mailOptions);
        res.json({ success: true, message: 'OTP sent successfully' });
    } catch (err) {
        res.json({ success: false, message: err.message });
    }
});

// Verify OTP
app.post('/api/auth/verify-otp', async (req, res) => {
    const { email, otp } = req.body;
    if (!email || !otp) return res.json({ success: false, message: 'Email and OTP required' });

    try {
        const [rows] = await db.query('SELECT * FROM otp_codes WHERE email = ? AND otp_code = ? AND is_used = 0 AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1', [email, otp]);
        
        if (rows.length === 0) {
            return res.json({ success: false, message: 'Invalid or expired OTP' });
        }

        await db.query('UPDATE otp_codes SET is_used = 1 WHERE id = ?', [rows[0].id]);
        res.json({ success: true, message: 'OTP verified successfully' });
    } catch (err) {
        res.json({ success: false, message: err.message });
    }
});

// Reset Password
app.post('/api/auth/reset-password', async (req, res) => {
    const { email, password } = req.body;
    if (!email || !password) return res.json({ success: false, message: 'Email and password required' });

    try {
        const hashedPassword = await bcrypt.hash(password, 10);
        await db.query('INSERT INTO users (email, password, is_verified) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE password = ?, is_verified = 1', [email, hashedPassword, hashedPassword]);
        res.json({ success: true, message: 'Password reset successful' });
    } catch (err) {
        res.json({ success: false, message: err.message });
    }
});

// --- SENSOR API ENDPOINT ---
// Protected with JWT
app.get('/api/status', authenticateToken, (req, res) => {
    res.json({
        gas: sensorData.gas || 0,
        api: sensorData.api || 0,
        reports: sensorReports,
        status: "ONLINE"
    });
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(`Node.js server running on port ${PORT}`);
});
