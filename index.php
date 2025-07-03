<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <title>POS EXPERIMENT</title>
  <?php
    session_start();
    if (!isset($_SESSION['login_id']))
      header('location:login.php');
    include('./header.php');
  ?>
  <style>
    :root {
      --sidebar-width: 260px;
      --sidebar-collapsed-width: 70px;
    }

    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f0f2f5;
      overflow-x: hidden;
      transition: all 0.3s ease;
    }

    #sidebar {
      position: fixed;
      top: 0;
      left: 0;
      bottom: 0;
      width: var(--sidebar-width);
      background: #1f2937;
      color: white;
      z-index: 1000;
      padding-top: 60px;
      transition: all 0.3s ease;
    }

    #sidebar a {
      display: flex;
      align-items: center;
      color: #cbd5e0;
      padding: 14px 24px;
      text-decoration: none;
      font-size: 15px;
      border-left: 4px solid transparent;
      transition: all 0.2s ease;
    }

    #sidebar a:hover,
    #sidebar a.active {
      background-color: #374151;
      border-left: 4px solid #3b82f6;
      color: #fff;
    }

    #sidebar .icon-field {
      margin-right: 12px;
      min-width: 20px;
      text-align: center;
    }

    #sidebar.active a span.text {
      display: none;
    }

    #sidebar.active {
      width: var(--sidebar-collapsed-width);
    }

    main#view-panel {
      margin-left: var(--sidebar-width);
      width: calc(100% - var(--sidebar-width));
      padding: 1rem;
      transition: all 0.3s ease;
    }

    #sidebar.active ~ main#view-panel {
      margin-left: var(--sidebar-collapsed-width);
      width: calc(100% - var(--sidebar-collapsed-width));
    }

    .toast {
      position: fixed;
      top: 1rem;
      right: 1rem;
      z-index: 1055;
    }

    @media (max-width: 768px) {
      #sidebar {
        left: -250px;
      }

      #sidebar.active {
        left: 0;
      }

      main#view-panel {
        margin-left: 0;
        width: 100%;
        padding: 0.5rem;
      }
    }

    .topbar {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      background-color: #111827;
      padding: 10px 20px;
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: space-between;
      z-index: 1050;
    }

    .topbar .center-title {
      flex: 1;
      text-align: center;
      font-weight: 600;
      font-size: 18px;
    }

    .topbar .logout a {
      color: #e5e7eb;
      text-decoration: none;
      transition: color 0.3s ease;
    }

    .topbar .logout a:hover {
      color: #f87171;
    }

    .topbar .logo {
      background: white;
      color: #1f2937;
      border-radius: 50%;
      padding: 6px 13px;
      font-size: 20px;
      cursor: pointer;
      transition: background 0.3s;
    }

    .topbar .logo:hover {
      background: #e5e7eb;
    }
  </style>
</head>
<body>
  <div class="topbar">
    <div class="logo" id="sidebarToggle"><i class="fa fa-bars"></i></div>
    <div class="center-title">POS EXPERIMENT</div>
    <div class="logout">
      <a href="ajax.php?action=logout">
        <?php echo $_SESSION['login_name'] ?> <i class="fa fa-power-off"></i>
      </a>
    </div>
  </div>

  <div id="sidebar">
    <a href="index.php?page=home" class="nav-item nav-home">
      <span class="icon-field"><i class="fa fa-home"></i></span>
      <span class="text">Home</span>
    </a>
    <?php if ($_SESSION['login_type'] == 1): ?>
      <a href="index.php?page=charts" class="nav-item nav-charts">
        <span class="icon-field"><i class="fa fa-chart-bar"></i></span>
        <span class="text">Charts</span>
      </a>
    <?php endif; ?>
    <a href="index.php?page=inventory" class="nav-item nav-inventory">
      <span class="icon-field"><i class="fa fa-list"></i></span>
      <span class="text">Inventory</span>
    </a>
    <?php if ($_SESSION['login_type'] == 1): ?>
      <a href="index.php?page=sales" class="nav-item nav-sales">
        <span class="icon-field"><i class="fa fa-coins"></i></span>
        <span class="text">Sales Record</span>
      </a>
      <a href="index.php?page=receiving" class="nav-item nav-receiving">
        <span class="icon-field"><i class="fa fa-file-alt"></i></span>
        <span class="text">Receiving</span>
      </a>
      <a href="index.php?page=categories" class="nav-item nav-categories">
        <span class="icon-field"><i class="fa fa-list"></i></span>
        <span class="text">Category List</span>
      </a>
      <a href="index.php?page=product" class="nav-item nav-product">
        <span class="icon-field"><i class="fa fa-boxes"></i></span>
        <span class="text">Product List</span>
      </a>
      <a href="index.php?page=supplier" class="nav-item nav-supplier">
        <span class="icon-field"><i class="fa fa-truck-loading"></i></span>
        <span class="text">Supplier List</span>
      </a>
    <?php endif; ?>
    <a href="index.php?page=customer" class="nav-item nav-customer">
      <span class="icon-field"><i class="fa fa-user-friends"></i></span>
      <span class="text">Customer List</span>
    </a>
    <?php if ($_SESSION['login_type'] == 1): ?>
      <a href="index.php?page=users" class="nav-item nav-users">
        <span class="icon-field"><i class="fa fa-users"></i></span>
        <span class="text">Users</span>
      </a>
    <?php endif; ?>
  </div>

  <!-- Toast -->
  <div class="toast" id="alert_toast" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-body text-white"></div>
  </div>

  <main id="view-panel">
    <?php $page = isset($_GET['page']) ? $_GET['page'] : 'home'; ?>
    <?php include $page . '.php'; ?>
  </main>

  <div id="preloader"></div>
  <a href="#" class="back-to-top d-block position-fixed" style="bottom: 20px; right: 20px; z-index: 9999;"><i class="icofont-simple-up"></i></a>

  <!-- Confirm Modal -->
  <div class="modal fade" id="confirm_modal" role="dialog">
    <div class="modal-dialog modal-md">
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Confirmation</h5></div>
        <div class="modal-body"><div id="delete_content"></div></div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="confirm">Continue</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Universal Modal -->
  <div class="modal fade" id="uni_modal" role="dialog">
    <div class="modal-dialog modal-md">
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title"></h5></div>
        <div class="modal-body"></div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="submit" onclick="$('#uni_modal form').submit()">Save</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    const sidebar = document.getElementById('sidebar');
    const toggle = document.getElementById('sidebarToggle');

    toggle.addEventListener('click', () => {
      sidebar.classList.toggle('active');
    });

    document.querySelectorAll('#sidebar a').forEach(link => {
      link.addEventListener('click', () => {
        if (window.innerWidth <= 768) {
          sidebar.classList.remove('active');
        }
      });
    });

    window.start_load = function () {
      document.body.insertAdjacentHTML('afterbegin', '<div id="preloader2"></div>');
    };

    window.end_load = function () {
      const preloader = document.getElementById('preloader2');
      if (preloader) preloader.remove();
    };

    window.uni_modal = function ($title = '', $url = '') {
      start_load();
      $.ajax({
        url: $url,
        error: err => {
          console.error(err);
          alert("An error occurred");
        },
        success: function (resp) {
          if (resp) {
            $('#uni_modal .modal-title').html($title);
            $('#uni_modal .modal-body').html(resp);
            $('#uni_modal').modal('show');
            end_load();
          }
        }
      });
    };

    window._conf = function ($msg = '', $func = '', $params = []) {
      $('#confirm_modal #confirm').attr('onclick', $func + "(" + $params.join(',') + ")");
      $('#confirm_modal .modal-body').html($msg);
      $('#confirm_modal').modal('show');
    };

    window.alert_toast = function ($msg = 'TEST', $bg = 'success') {
      $('#alert_toast').removeClass('bg-success bg-danger bg-info bg-warning');
      $('#alert_toast').addClass('bg-' + $bg);
      $('#alert_toast .toast-body').html($msg);
      $('#alert_toast').toast({ delay: 3000 }).toast('show');
    };

    $(document).ready(function () {
      $('#preloader').fadeOut('fast', function () {
        $(this).remove();
      });
    });
  </script>
</body>
</html>
