<nav class="navbar">
    <div class="container nav-inner">
        <a class="brand" href="/menu_management/index.php">MenuMS</a>
        <div class="nav-links">
            <a href="/menu_management/index.php">Menu</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/menu_management/customer/cart.php">Cart</a>
                <a href="/menu_management/customer/orders.php">My Orders</a>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="/menu_management/admin/dashboard.php">Admin</a>
                <?php endif; ?>
                <a href="/menu_management/logout.php">Logout</a>
            <?php else: ?>
                <a href="/menu_management/login.php">Login</a>
                <a href="/menu_management/register.php">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
