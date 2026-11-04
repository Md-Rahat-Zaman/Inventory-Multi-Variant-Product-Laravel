document.addEventListener('DOMContentLoaded', function () {

    const menuToggles = document.querySelectorAll('.sidebar-menu-toggle');

    menuToggles.forEach(function (toggle) {

        toggle.addEventListener('click', function (event) {

            const href = this.getAttribute('href');
            const isToggleClick = event.target.closest('.menu-arrow');

            if (href !== '#' && href !== '' && !isToggleClick) {
                return;
            }

            event.preventDefault();

            const currentMenu = this.closest('.sidebar-menu');

            // Close other menus
            document.querySelectorAll('.sidebar-menu.open').forEach(function (menu) {

                if (menu !== currentMenu) {
                    menu.classList.remove('open');
                }

            });

            // Toggle current menu
            currentMenu.classList.toggle('open');

        });

    });

});