<?php
function check_login($email, $password, $conn) {
    // Input validation to block suspicious inputs
    if (preg_match("/['\"=;]/", $email) || stripos($email, "OR") !== false) {
        return false;
    }

    $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user && password_verify($password, $user['password'])) {
        return true;
    }
    return false;
}
?>