document.addEventListener('DOMContentLoaded', function () {

    const themeToggle = document.getElementById('themeToggle');
    const iconSun = document.getElementById('iconSun');
    const iconMoon = document.getElementById('iconMoon');

    if (!themeToggle) {
        return;
    }

    // =====================================================
    // CEK TEMA YANG TERSIMPAN
    // =====================================================

    function getTheme() {
        try {
            return localStorage.getItem('ejurnal-theme');
        } catch (error) {
            return null;
        }
    }


    // =====================================================
    // SIMPAN TEMA
    // =====================================================

    function saveTheme(theme) {
        try {
            localStorage.setItem('ejurnal-theme', theme);
        } catch (error) {
            // Abaikan jika localStorage tidak tersedia
        }
    }


    // =====================================================
    // UPDATE ICON
    // =====================================================

    function updateIcon() {

        const isDark =
            document.documentElement.classList.contains('dark');

        if (iconSun) {
            iconSun.classList.toggle('hidden', !isDark);
        }

        if (iconMoon) {
            iconMoon.classList.toggle('hidden', isDark);
        }

    }


    // =====================================================
    // TERAPKAN TEMA
    // =====================================================

    function applyTheme(theme) {

        if (theme === 'dark') {

            document.documentElement.classList.add('dark');

        } else {

            document.documentElement.classList.remove('dark');

        }

        updateIcon();
    }


    // =====================================================
    // TEMA AWAL
    // =====================================================

    const savedTheme = getTheme();

    if (savedTheme === 'dark') {

        applyTheme('dark');

    } else if (savedTheme === 'light') {

        applyTheme('light');

    } else {

        // Jika belum pernah memilih tema,
        // ikuti pengaturan sistem

        const prefersDark =
            window.matchMedia &&
            window.matchMedia('(prefers-color-scheme: dark)').matches;

        applyTheme(prefersDark ? 'dark' : 'light');
    }


    // =====================================================
    // TOMBOL TOGGLE
    // =====================================================

    themeToggle.addEventListener('click', function () {

        const isDark =
            document.documentElement.classList.contains('dark');

        if (isDark) {

            applyTheme('light');
            saveTheme('light');

        } else {

            applyTheme('dark');
            saveTheme('dark');

        }

    });


    // =====================================================
    // UPDATE ICON SAAT HALAMAN DIBUKA
    // =====================================================

    updateIcon();

});