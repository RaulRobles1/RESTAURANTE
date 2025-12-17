<div class="header">
    <div class="user-welcome">
        Bienvenido, <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Invitado'; ?>
    </div>

    <nav class="nav-menu">
        <ul>
            <li><a href="Categorias.php">Inicio</a></li>
            <li><a href="Carrito.php">Carrito</a></li>
            <li><a href="logout.php">Cerrar sesión</a></li>
        </ul>
    </nav>
</div>