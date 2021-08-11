<!-- Content Wrapper -->
<div id="content-wrapper" class="d-flex flex-column">

<!-- Main Content -->
<div id="content" class="bg-gray-200">

    <!-- Topbar -->
    <nav class="navbar navbar-expand navbar-light bg-gray-100 topbar mb-4 static-top shadow">
        <img src="<?php echo base_url() ?>assets/img/33.png" width="80px" height="50px">
        <span class="h5 text-gray-800 ml-2 mt-2 font-weight-bold">
            <p class="mt-2">
                UPT. Pelayanan Jasa dan Kebandarudaraan Abdulrachman Saleh
                Kab. Malang
            </p>  
        </span>
    

        <!-- Sidebar Toggle (Topbar) -->
        <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
            <i class="fa fa-bars"></i>
        </button>

        <!-- Topbar Navbar -->
        <ul class="navbar-nav ml-auto">

        <!-- Nav Item - Search Dropdown (Visible Only XS) -->
            <li class="nav-item dropdown no-arrow d-sm-none">
                <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-search fa-fw">
                    </i>
                </a>
            </li>

            <!-- Nav Item - Alerts -->
            

            <div class="topbar-divider d-none d-sm-block">
                
            </div>

            <!-- Nav Item - User Information -->
            <li class="nav-item dropdown no-arrow">
                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?= $user['name']; ?></span>
                    <img class="img-profile rounded-circle"
                        src="<?= base_url('assets/img/profile/') . $user['image']; ?>">
                    
                </a>
                <!-- Dropdown - User Information -->
                <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                    aria-labelledby="userDropdown">
                    <a class="dropdown-item" href="<?= base_url('auth/logout'); ?>" data-toggle="modal" data-target="#logoutModal">
                        <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                        Keluar
                    </a>
                </div>
            </li>

        </ul>

    </nav>
    
    <!-- End of Topbar -->
