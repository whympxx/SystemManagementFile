    <!-- Footer -->
    <footer class="bg-white border-t border-secondary-200 mt-auto">
        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <p class="text-sm text-secondary-500">
                        &copy; <?php echo date('Y'); ?> FileManager Pro. All rights reserved.
                    </p>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="#" class="text-secondary-400 hover:text-secondary-500 transition duration-150 ease-in-out">
                        <i class="fab fa-github"></i>
                    </a>
                    <a href="#" class="text-secondary-400 hover:text-secondary-500 transition duration-150 ease-in-out">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <div class="flex items-center text-sm text-secondary-500">
                        <i class="fas fa-code mr-1"></i>
                        Version 1.0
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="<?php echo strpos($_SERVER['REQUEST_URI'], '/pages/') !== false ? '../' : ''; ?>js/main.js"></script>
    <script>
        // Toggle user menu
        document.getElementById('user-menu-button')?.addEventListener('click', function() {
            const menu = document.getElementById('user-menu');
            menu.classList.toggle('hidden');
        });

        // Toggle mobile menu
        document.getElementById('mobile-menu-button')?.addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });

        // Close menus when clicking outside
        document.addEventListener('click', function(event) {
            const userMenu = document.getElementById('user-menu');
            const userMenuButton = document.getElementById('user-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            const mobileMenuButton = document.getElementById('mobile-menu-button');

            if (userMenu && !userMenuButton.contains(event.target) && !userMenu.contains(event.target)) {
                userMenu.classList.add('hidden');
            }

            if (mobileMenu && !mobileMenuButton.contains(event.target) && !mobileMenu.contains(event.target)) {
                mobileMenu.classList.add('hidden');
            }
        });
    </script>
</body>
</html>
