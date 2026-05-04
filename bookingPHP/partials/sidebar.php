<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$username = $_SESSION['username'] ?? 'User';
$role = $_SESSION['role'] ?? '';
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');

$navItems = [
    ['label' => 'Dashboard', 'href' => 'dashboard.php'],
    ['label' => 'My tickets', 'href' => 'my_tickets.php'],
    ['label' => 'Rate', 'href' => 'booking.php'],
    ['label' => 'My ratings', 'href' => 'my_bookings.php'],
];

$isAdmin = ($role === 'admin');
$isOnAdminPage = $isAdmin && str_starts_with($currentPage, 'admin_');
?>

<!-- Sidebar (offcanvas on mobile, fixed on lg+) -->
<div class="offcanvas-lg offcanvas-start booking-sidebar" tabindex="-1" id="bookingSidebar" aria-labelledby="bookingSidebarLabel">
  <div class="offcanvas-header d-lg-none">
    <h5 class="offcanvas-title" id="bookingSidebarLabel">Menu</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>

  <div class="offcanvas-body d-flex flex-column p-3">
    <div class="d-none d-lg-block mb-3">
      <a class="booking-sidebar__brand text-decoration-none" href="dashboard.php">Booking</a>
      <div class="booking-sidebar__subtitle">tickets &amp; ratings</div>
    </div>

    <ul class="nav nav-pills flex-column gap-1">
      <?php if (!$isOnAdminPage): ?>
        <?php foreach ($navItems as $item): ?>
          <?php $isActive = ($currentPage === $item['href']) ? 'active' : ''; ?>
          <li class="nav-item">
            <a class="nav-link <?php echo $isActive; ?>" href="<?php echo $item['href']; ?>">
              <?php echo htmlspecialchars($item['label']); ?>
            </a>
          </li>
        <?php endforeach; ?>
      <?php endif; ?>

      <?php if ($isAdmin): ?>
        <li class="mt-3 small text-uppercase text-white-50 px-2">
          Admin
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo ($currentPage === 'admin_dashboard.php') ? 'active' : ''; ?>" href="admin_dashboard.php">
            Overview
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo ($currentPage === 'admin_users.php') ? 'active' : ''; ?>" href="admin_users.php">
            Users
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo ($currentPage === 'admin_events.php') ? 'active' : ''; ?>" href="admin_events.php">
            Events
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo ($currentPage === 'admin_tickets.php') ? 'active' : ''; ?>" href="admin_tickets.php">
            Tickets
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo ($currentPage === 'admin_ratings.php') ? 'active' : ''; ?>" href="admin_ratings.php">
            Ratings
          </a>
        </li>
      <?php endif; ?>
    </ul>

    <div class="mt-auto pt-3 booking-sidebar__footer">
      <div class="booking-sidebar__user">
        <div class="booking-sidebar__username"><?php echo htmlspecialchars($username); ?></div>
        <div class="booking-sidebar__role"><?php echo htmlspecialchars($role ?: 'user'); ?></div>
      </div>
      <a href="logout.php" class="btn btn-danger w-100 mt-2">Logout</a>
    </div>
  </div>
</div>

