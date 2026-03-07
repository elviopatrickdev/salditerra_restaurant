<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>login | Register</title>

    <!-- Meta Tags -->

    <meta name="description"
        content="Salditerra Restaurant in Abuja, Nigeria, offers authentic Cape Verdean cuisine with traditional dishes made from fresh ingredients and rich island flavors.">
    <meta name="keywords"
        content="Salditerra Restaurant Abuja, Cape Verdean food, Cape Verdean restaurant in Abuja, traditional Cape Verde cuisine, African cuisine Abuja, cachupa, Cape Verde food Nigeria">
    <meta name="category" content="Restaurant / Cape Verdean Cuisine">
    <meta name="author" content="Elvio Patrick">

    <!-- Custom Style Links -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer">
    <link
        href="https://fonts.googleapis.com/css2?family=Noto+Serif:wght@400;600;700&family=Noto+Sans:wght@300;400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="assets/favicon.png" type="image/png">

    <!-- jQuery (necessário para AJAX) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <style>
        body {
            background-image: url('assets/pattern-food.png');
            background-repeat: repeat;
            background-size: 60%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .container {
            position: relative;
            width: 100%;
            height: 630px;
            background-color: #181A18;
            border: 8px solid #151D21;
            border-radius: 30px;
            box-shadow:
                inset 0 6px 10px rgba(0, 0, 0, 0.5),
                0 12px 25px rgba(0, 0, 0, 0.7);
            overflow: hidden;
        }

        .form-box {
            position: absolute;
            display: flex;
            align-items: center;
            bottom: 0;
            right: 0;
            width: 100%;
            height: 70%;
            text-align: center;
            padding: 40px;
            z-index: 1;
            transition: .6s ease-in-out .6s, visibility 0s .8s;
        }

        .form-box #register-form {
            margin-top: 30px;
        }

        .container.active .form-box {
            right: 0;
            bottom: 30%;
        }

        .form-box.login {
            padding-top: 0;
        }

        .form-box.register {
            visibility: hidden;
        }

        .container.active .form-box.register {
            visibility: visible;
        }

        .container.active .form-box.login {
            visibility: hidden;
        }

        form {
            width: 100%;
        }

        .container h1 {
            font-size: 28px;
            margin: -10px 0;
            text-shadow: 0 1px 2px rgba(0, 0, 0, .6)
        }

        .input-box {
            position: relative;
            margin: 4px 0;
        }

        .input-box label {
            width: 100%;
            text-align: left;
            font-size: 12px;
            color: #ccc;
        }

        .input-box input {
            width: 100%;
            padding: 5px 50px 5px 20px;
            background: #ccc;
            border-radius: 8px;
            border: none;
            outline: none;
            font-size: 14px;
            color: #333;
            font-weight: 500;
        }

        .input-box input::placeholder {
            color: #888;
            font-weight: 400;
        }

        .input-box i {
            position: absolute;
            right: 20px;
            top: 70%;
            transform: translateY(-50%);
            font-size: 14px;
            color: #888;
        }

        .input-box select,
        .input-box input[type="file"] {
            padding: 4px 12px;
            margin-right: 10px;
            background: #ccc;
            color: #888;
            border-radius: 6px;
            font-size: 12px;
            transition: border-color 0.2s;
        }

        .input-box select {
            padding: 7px 12px;
        }

        .input-box select:focus,
        .input-box input[type="file"]:focus {
            color: #181A18;
        }

        .btn {
            width: 100%;
            height: 40px;
            margin-top: 4px;
            background-color: darkgoldenrod;
            border-radius: 8px;
            border: 3px solid darkgoldenrod;
            cursor: pointer;
            font-size: 14px;
            color: #1B2428;
            font-weight: 600;
        }

        .btn:hover {
            background-color: transparent;
            border: 3px solid darkgoldenrod;
            color: darkgoldenrod;
        }

        .container p {
            font-size: 13.5px;
            margin: 15px 0;
        }

        .back-index-btn a {
            position: absolute;
            right: -50px;
            bottom: -45px;
            width: 120px;
            height: 120px;
            border: 3px solid darkgoldenrod;
            border-radius: 50%;
            box-shadow:
                inset 0 4px 8px rgba(0, 0, 0, 0.5),
                inset 0 -4px 6px rgba(0, 0, 0, 0.5),
                0 8px 20px rgba(0, 0, 0, 0.7);

            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 26px;
            color: #333;
            text-decoration: none;
            background-color: darkgoldenrod;
            z-index: 3000;
        }

        .back-index-btn a i {
            position: absolute;
            left: 25px;
            bottom: 60px;
        }

        .back-index-btn a:hover {
            background-color: #333;
            color: #ccc;
        }

        .toggle-box {
            position: absolute;
            width: 100%;
            height: 100%;
        }

        .toggle-box::before {
            content: '';
            position: absolute;
            left: -12px;
            top: -270%;
            width: 100%;
            height: 300%;
            background: darkgoldenrod;
            z-index: 2;
            border-radius: 150px;
            transition: 1s ease-in-out;
        }

        .container.active .toggle-box::before {
            top: 70%;
        }

        .toggle-painel {
            position: absolute;
            width: 100%;
            height: 30%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            z-index: 2;
            transition: .6s ease-in-out;
        }

        .toggle-painel.toggle-left {
            top: 0;
            left: -12px;
        }

        .container.active .toggle-painel.toggle-left {
            top: -30%;
        }

        .toggle-painel.toggle-right {
            left: -12px;
            bottom: -30%;
        }

        .container.active .toggle-painel.toggle-right {
            bottom: -10px;
        }

        .toggle-painel h1,
        .toggle-painel p {
            color: #1B2428;
            text-align: center;
        }

        .toggle-painel .btn {
            width: 160px;
            height: 46px;
            background-color: #1B2428;
            color: darkgoldenrod;
            border: 3px solid #1B2428;
        }

        .toggle-painel .btn:hover {
            background-color: transparent;
            color: #1B2428;
        }

        /* ===== ERROR MENSSAGE ===== */
        .message-box {
            min-height: 22px;
            margin-top: 8px;
            font-size: 13px;
            font-weight: 500;
            color: #ff4d4d;
        }

        /* =========================
   IPAD & DESKTOP (>= 768px)
========================= */

        @media (min-width: 768px) {

            body {
                background-size: 50%;
                background-position: -350px 0;
            }

            .container {
                width: 850px;
                height: 580px;
            }

            .form-box {
                right: 0;
                width: 50%;
                height: 100%;
            }

            .container.active .form-box {
                right: 50%;
                bottom: 0;
            }

            .container h1 {
                font-size: 36px;
            }

            .input-box {
                margin: 10px 0;
            }

            .input-box input {
                padding: 8px 50px 8px 20px;
                font-size: 16px;
            }

            .input-box select,
            .input-box input[type="file"] {
                padding: 6px 12px;
                font-size: 16px;
            }

            .input-box select {
                padding: 10px 12px;
            }

            .btn {
                height: 48px;
                font-size: 16px;
            }

            .container p {
                font-size: 14.5px;
            }

            .toggle-box::before {
                left: -252%;
                top: 0;
                width: 300%;
                height: 100%;
            }

            .container.active .toggle-box::before {
                top: 0;
                left: 50%;
            }

            .toggle-painel {
                width: 50%;
                height: 100%;
            }

            .toggle-painel.toggle-left {
                left: 0;
                top: 0;
            }

            .container.active .toggle-painel.toggle-left {
                left: -50%;
                top: 0;
            }

            .toggle-painel.toggle-right {
                right: -50%;
                bottom: 0;
                left: auto;
            }

            .container.active .toggle-painel.toggle-right {
                right: 0;
            }

            @media (min-width: 1440px) {
                body {
                    background-size: 30%;
                    background-position: -380px 0;
                }
            }
        }
    </style>

</head>

<body>

    <div class="container" id="container">
        <div class="back-index-btn">
            <a href="index.php" class="icon"><i class="fa-solid fa-arrow-rotate-left"></i></a>
        </div>
        <div class="form-box login">
            <form id="login-form">
                <h1 class="mb-3">Login</h1>
                <div class="input-box">
                    <label for="login-username">Username:</label>
                    <input type="text" id="login-username" name="username" placeholder="Username" required>
                    <i class="fa-solid fa-user"></i>
                </div>
                <div class="input-box">
                    <label for="login-password">Password:</label>
                    <input type="password" id="login-password" name="password" placeholder="Password" required>
                    <i class="fa-solid fa-lock"></i>
                </div>
                <button type="submit" class="btn mt-3">Login</button>
                <div id="login-message" class="message-box"></div>
            </form>
        </div>

        <div class="form-box register">
            <form id="register-form" enctype="multipart/form-data">
                <h1>Registration</h1>
                <div class="input-box">
                    <label for="register-username">Username:</label>
                    <input type="text" id="register-username" name="register-username" placeholder="Username" required>
                    <i class="fa-solid fa-user"></i>
                </div>
                <div class="input-box">
                    <label for="register-email">Email:</label>
                    <input type="email" id="register-email" name="register-email" placeholder="Email" required>
                    <i class="fa-solid fa-envelope"></i>
                </div>
                <div class="input-box">
                    <label for="register-password">Password:</label>
                    <input type="password" id="register-password" name="register-password" placeholder="Password" required>
                    <i class="fa-solid fa-lock"></i>
                </div>
                <div class="input-box">
                    <label for="register-confirm-password">Confirm your Password:</label>
                    <input type="password" id="register-confirm-password" name="register-confirm-password" placeholder="confirm password" required>
                    <i class="fa-solid fa-lock"></i>
                </div>
                <div class="input-box d-flex flex-row">
                    <div class="w-50 d-flex flex-column justify-content-start mt-1">
                        <label for="register-user_type">User type:</label>
                        <select id="register-user_type" name="register-user_type" required>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="w-50">
                        <label for="register-profile_pic">Profile photo:</label>
                        <input type="file" id="register-profile_pic" name="register-profile_pic">

                    </div>
                </div>
                <button type="submit" class="btn">Register</button>
                <div id="register-error-message" class="message-box"></div>
            </form>
        </div>

        <div class="toggle-box">
            <div class="toggle-painel toggle-left">
                <h1>Hello, Welcome!</h1>
                <p>Don't have an account?</p>
                <button class="btn register-btn">Register</button>
            </div>
            <div class="toggle-painel toggle-right">
                <h1>Welcome Back!</h1>
                <p>Already have an account?</p>
                <button class="btn login-btn">Login</button>
            </div>
        </div>

    </div>

    <script>
        // Toggle Login/Register
        const container = document.querySelector('.container');
        document.querySelector('.register-btn').addEventListener('click', () => {
            container.classList.add('active');
        });
        document.querySelector('.login-btn').addEventListener('click', () => {
            container.classList.remove('active');
        });

        // LOGIN AJAX
        $('#login-form').submit(function(event) {
            event.preventDefault();
            let username = $('#login-username').val().trim();
            let password = $('#login-password').val();

            if (!username || !password) {
                $('#login-message').text('Preencha todos os campos.');
                return;
            }
            $.ajax({
                url: '../auth/process_login.php',
                type: 'POST',
                data: {
                    username,
                    password
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        window.location.href = response.redirect;
                    } else {
                        $('#login-message').text(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    $('#login-message').text('Error processing login: ' + error);
                }
            });
        });


        // REGISTER AJAX
        $('#register-form').submit(function(event) {
            event.preventDefault();
            let errors = [];
            let username = $('#register-username').val().trim();
            let email = $('#register-email').val().trim();
            let password = $('#register-password').val();
            let confirmPassword = $('#register-confirm-password').val();
            let profilePic = $('#register-profile_pic')[0].files[0];


            if (username.length < 3) errors.push('Username must be at least 3 characters long.');
            let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email)) errors.push('Invalid email.');
            if (password.length < 6) errors.push('Password must be at least 6 characters long.');
            if (password !== confirmPassword) errors.push('Passwords do not match.');
            if (!profilePic) errors.push('Upload a profile photo.');
            else {
                let allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
                if (!allowedTypes.includes(profilePic.type)) errors.push('Please upload JPG, PNG, or WEBP.');
                if (profilePic.size > 2 * 1024 * 1024) errors.push('Please upload image <=2MB.');
            }


            if (errors.length > 0) {
                $('#register-error-message').text(errors.join("\n"));
                return;
            }


            let formData = new FormData(this);
            $.ajax({
                url: '../auth/process_register.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) window.location.href = 'login-register.php';
                    else $('#register-error-message').text(response.message);
                },
                error: function(xhr, status, error) {
                    $('#register-error-message').text('Error processing registration: ' + error);
                }
            });
        });
    </script>

</body>

</html>