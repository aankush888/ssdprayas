  </div>
</div>

<script>
document.getElementById('sideToggle')?.addEventListener('click', function () {
    document.getElementById('sidebar').classList.toggle('is-open');
});
document.querySelectorAll('[data-confirm]').forEach(function (el) {
    el.addEventListener('click', function (ev) {
        if (!confirm(el.dataset.confirm)) ev.preventDefault();
    });
});
</script>
</body>
</html>
