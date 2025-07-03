<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Login | Grocery & Restaurant System</title>
  <?php include('./header.php'); ?>
  <?php include('./db_connect.php'); ?>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <style>
    body {
        margin: 0;
        padding: 0;
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(to right, #4CAF50, #2196F3);
    }

    main#main {
        display: flex;
        height: 100vh;
        overflow: hidden;
    }

    #login-left {
        flex: 1;
        background: url('grocery-bg.jpg') center center no-repeat;
        background-size: cover;
        display: flex;
        justify-content: center;
        align-items: center;
        color: white;
        position: relative;
    }

    #login-left::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
    }

    #login-left .logo {
        position: relative;
        font-size: 5rem;
        text-align: center;
    }

    #login-left .logo span {
        font-size: 7rem;
        color: #ffcc00;
    }

    #login-right {
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
        background: white;
        box-shadow: -5px 0 15px rgba(0, 0, 0, 0.2);
    }

    .card {
        width: 90%;
        max-width: 400px;
        padding: 2rem;
        border: none;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
        transition: transform 0.3s;
    }

    .card:hover {
        transform: translateY(-5px);
    }

    h4 {
        text-align: center;
        margin-bottom: 1rem;
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        color: #2c7a2c;
    }

    .form-group label {
        font-weight: 500;
        font-size: 0.9rem;
        color: #2c7a2c;
    }

    .form-control {
        border-radius: 5px;
        height: 45px;
        border: 1px solid #2c7a2c;
    }

    .form-control:focus {
        box-shadow: 0 0 8px rgba(44, 122, 44, 0.5);
        border-color: #2c7a2c;
    }

    .btn-primary {
        background: #2c7a2c;
        border: none;
        border-radius: 5px;
        height: 45px;
        font-size: 1rem;
        color: white;
        transition: background 0.3s, box-shadow 0.3s;
    }

    .btn-primary:hover {
        background: #1e5e1e;
        box-shadow: 0 4px 8px rgba(44, 122, 44, 0.5);
    }

    .alert {
        margin-top: 1rem;
    }

    @media (max-width: 768px) {
        #login-left {
            display: none;
        }

        #login-right {
            width: 100%;
        }

        .card {
            width: 100%;
            padding: 1.5rem;
        }
    }
  </style>
</head>
<body>
  <main id="main">
    <div id="login-left">
      <div class="logo">
        <span class="fa fa-apple-alt"></span>
        <p>Grocery & Restaurant System</p>
      </div>
    </div>
    <div id="login-right">
      <div class="card">
        <h4>Welcome Back!</h4>
        <form id="login-form" method="POST">
          <div class="form-group">
            <label for="username" class="control-label">Username</label>
            <input type="text" id="username" name="username" class="form-control" required autocomplete="on">
          </div>
          <div class="form-group">
            <label for="password" class="control-label">Password</label>
            <input type="password" id="password" name="password" class="form-control" required autocomplete="on
            ">
          </div>

          <?php if (isset($error_message)): ?>
            <div class="alert alert-danger" role="alert"><?php echo $error_message; ?></div>
          <?php endif; ?>

          <div class="form-group text-center">
            <button type="submit" class="btn btn-primary btn-block">Login</button>
          </div>
        </form>
      </div>
    </div>
  </main>

  <script>
    $('#login-form').submit(function (e) {
        e.preventDefault();
        const submitBtn = $('#login-form button[type="submit"]');
        submitBtn.attr('disabled', true).text('Logging in...');
        
        if ($(this).find('.alert-danger').length > 0)
            $(this).find('.alert-danger').remove();

        $.ajax({
            url: 'ajax.php?action=login',
            method: 'POST',
            data: $(this).serialize(),
            error: function () {
                console.log('An error occurred.');
                submitBtn.removeAttr('disabled').text('Login');
            },
            success: function (resp) {
                if (resp == 1) {
                    location.href = 'index.php?page=home';
                } else if (resp == 2) {
                    location.href = 'voting.php';
                } else {
                    $('#login-form').prepend('<div class="alert alert-danger" role="alert">Username or password is incorrect.</div>');
                    submitBtn.removeAttr('disabled').text('Login');
                }
            }
        });
    });
  </script>
</body>
</html>
