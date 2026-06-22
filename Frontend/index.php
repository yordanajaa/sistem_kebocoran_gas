<!DOCTYPE html>
<html>
<head>
    <title>Redirecting...</title>
    <script>
        if (localStorage.getItem('token')) {
            window.location.href = 'dashboard.php';
        } else {
            window.location.href = 'login.php';
        }
    </script>
</head>
<body>
</body>
</html>
