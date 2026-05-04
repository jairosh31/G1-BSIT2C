<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$username = $_SESSION['username'] ?? 'User';
$role = $_SESSION['role'] ?? '';
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');

$navItems = [
    ['label' => 'Dashboard', 'href' => 'dashboard.php'],
    ['label' => 'Book ticket', 'href' => 'ticket_booking.php'],
    ['label' => 'My tickets', 'href' => 'my_tickets.php'],
    ['label' => 'Rate', 'href' => 'booking.php'],
    ['label' => 'My ratings', 'href' => 'my_bookings.php'],
];
?>

<nav class="navbar navbar-expand-lg navbar-dark booking-navbar sticky-top">
  <div class="container">
    <a class="navbar-brand fw-semibold" href="dashboard.php">Booking</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#bookingNavbar"
      aria-controls="bookingNavbar" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="bookingNavbar">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <?php foreach ($navItems as $item): ?>
          <?php $isActive = ($currentPage === $item['href']) ? 'active' : ''; ?>
          <li class="nav-item">
            <a class="nav-link <?php echo $isActive; ?>" href="<?php echo $item['href']; ?>">
              <?php echo htmlspecialchars($item['label']); ?>
            </a>
          </li>
        <?php endforeach; ?>

        <?php if ($role === 'admin'): ?>
          <li class="nav-item">
            <a class="nav-link <?php echo ($currentPage === 'admin_dashboard.php') ? 'active' : ''; ?>" href="admin_dashboard.php">
              Admin
            </a>
          </li>
        <?php endif; ?>
      </ul>

      <ul class="navbar-nav ms-auto">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <?php echo htmlspecialchars($username); ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li>
              <h6 class="dropdown-header">
                Signed in<?php echo ($role === 'admin') ? ' (admin)' : ''; ?>
              </h6>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

