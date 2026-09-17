</div>

<script>

const menuButton = document.getElementById('menuButton');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('sidebarOverlay');

if (menuButton && sidebar && overlay) {

    menuButton.addEventListener('click', function () {

        sidebar.classList.toggle('-translate-x-full');

        overlay.classList.toggle('hidden');

    });

    overlay.addEventListener('click', function () {

        sidebar.classList.add('-translate-x-full');

        overlay.classList.add('hidden');

    });

}

</script>

</body>

</html>