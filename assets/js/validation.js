/**
 * RecipeNet - Client-side Validation
 * Χρησιμοποιεί HTML5 Constraint Validation API + custom checks.
 */

(function ($) {
    'use strict';

    // ----- Εγγραφή -----
    const $regForm = $('#registerForm');
    if ($regForm.length) {
        const $pw  = $('#password');
        const $pw2 = $('#password2');
        const $bar = $('#pwStrength');

        $pw.on('input', function () {
            const v = $pw.val();
            let score = 0;
            if (v.length >= 8)       score++;
            if (/[A-Z]/.test(v))     score++;
            if (/[0-9]/.test(v))     score++;
            if (/[^A-Za-z0-9]/.test(v)) score++;
            const pct = (score / 4) * 100;
            $bar.css('width', pct + '%')
                .removeClass('weak medium strong')
                .addClass(score <= 1 ? 'weak' : score === 2 ? 'medium' : 'strong');
        });

        $regForm.on('submit', function (e) {
            if ($pw.val() !== $pw2.val()) {
                e.preventDefault();
                Swal.fire({ icon: 'error', title: 'Οι κωδικοί δεν ταιριάζουν!' });
                return false;
            }
            if ($pw.val().length < 8) {
                e.preventDefault();
                Swal.fire({ icon: 'warning', title: 'Ο κωδικός πρέπει να είναι ≥ 8 χαρακτήρες.' });
                return false;
            }
        });
    }

    // ----- Φόρμα συνταγής -----
    const $rec = $('#recipeForm');
    if ($rec.length) {
        // Preview εικόνας
        $('#imageInput').on('change', function () {
            const file = this.files[0];
            const $prev = $('#imagePreview');
            if (file && file.type.startsWith('image/')) {
                if (file.size > 5 * 1024 * 1024) {
                    Swal.fire({ icon: 'error', title: 'Η εικόνα ξεπερνά τα 5 MB.' });
                    $(this).val('');
                    $prev.addClass('d-none');
                    return;
                }
                const reader = new FileReader();
                reader.onload = e => $prev.attr('src', e.target.result).removeClass('d-none');
                reader.readAsDataURL(file);
            } else {
                $prev.addClass('d-none');
            }
        });

        $rec.on('submit', function (e) {
            const title = $('input[name=title]').val().trim();
            const ing   = $('textarea[name=ingredients]').val().trim();
            const stp   = $('textarea[name=steps]').val().trim();
            if (title.length < 3) { e.preventDefault(); Swal.fire({icon:'warning',title:'Τίτλος ≥ 3 χαρακτήρες.'}); return; }
            if (!ing) { e.preventDefault(); Swal.fire({icon:'warning',title:'Συμπληρώστε τα υλικά.'}); return; }
            if (!stp) { e.preventDefault(); Swal.fire({icon:'warning',title:'Συμπληρώστε τα βήματα.'}); return; }
        });
    }
})(jQuery);
