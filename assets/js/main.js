/**
 * RecipeNet - Main JS
 * Διαχείριση AJAX αλληλεπιδράσεων (likes, σχόλια).
 */

(function ($) {
    'use strict';

    const CSRF = $('meta[name="csrf-token"]').attr('content');

    // ---------- LIKE ----------
    $(document).on('click', '.like-btn', function () {
        const $btn = $(this);
        const recipeId = $btn.data('recipe-id');

        $.ajax({
            url: 'api/like.php',
            type: 'POST',
            dataType: 'json',
            headers: { 'X-CSRF-Token': CSRF },
            data: { recipe_id: recipeId }
        })
        .done(function (res) {
            if (!res.ok) {
                Swal.fire({ icon: 'error', title: res.error || 'Σφάλμα' });
                return;
            }
            const $icon = $btn.find('.bi');
            const $cnt  = $btn.find('.like-count');
            $cnt.text(res.count);
            $btn.addClass('like-pop');
            setTimeout(() => $btn.removeClass('like-pop'), 300);

            if (res.liked) {
                $icon.removeClass('bi-heart').addClass('bi-heart-fill text-danger');
                $btn.addClass('liked');
                if ($btn.hasClass('btn-outline-danger')) $btn.removeClass('btn-outline-danger').addClass('btn-danger');
            } else {
                $icon.removeClass('bi-heart-fill text-danger').addClass('bi-heart');
                $btn.removeClass('liked');
                if ($btn.hasClass('btn-danger')) $btn.removeClass('btn-danger').addClass('btn-outline-danger');
            }
        })
        .fail(function (xhr) {
            const err = xhr.responseJSON?.error || 'Σφάλμα δικτύου';
            Swal.fire({
                icon: 'warning', title: err,
                showCancelButton: xhr.status === 401, confirmButtonText: 'Σύνδεση',
                cancelButtonText: 'Άκυρο'
            }).then(r => { if (r.isConfirmed && xhr.status === 401) location.href = 'login.php'; });
        });
    });

    // ---------- COMMENT ----------
    const $cf = $('#commentForm');
    if ($cf.length) {
        $cf.on('submit', function (e) {
            e.preventDefault();
            const recipeId = $cf.data('recipe-id');
            const $ta = $cf.find('textarea[name=content]');
            const content = $ta.val().trim();
            if (!content) {
                Swal.fire({ icon: 'warning', title: 'Γράψε ένα σχόλιο πρώτα.' });
                return;
            }

            $.ajax({
                url: 'api/comment.php',
                type: 'POST',
                dataType: 'json',
                headers: { 'X-CSRF-Token': CSRF },
                data: { recipe_id: recipeId, content: content }
            })
            .done(function (res) {
                if (!res.ok) { Swal.fire({ icon: 'error', title: res.error }); return; }
                const c = res.comment;
                const safeContent = $('<div>').text(c.content).html().replace(/\n/g, '<br>');
                const html = `
                    <div class="card mb-2 comment-new">
                        <div class="card-body py-2">
                            <strong><a href="profile.php?id=${c.user_id}" class="text-decoration-none">${$('<div>').text(c.username).html()}</a></strong>
                            <small class="text-muted ms-2">${c.time_ago}</small>
                            <p class="mb-0">${safeContent}</p>
                        </div>
                    </div>`;
                $('#commentList').prepend(html);
                $ta.val('');
                $('#commentTotal').text(parseInt($('#commentTotal').text() || '0', 10) + 1);
            })
            .fail(function (xhr) {
                Swal.fire({ icon: 'error', title: xhr.responseJSON?.error || 'Σφάλμα' });
            });
        });
    }

    // ---------- Auto-dismiss alerts ----------
    setTimeout(() => $('.alert-dismissible').alert('close'), 5000);

})(jQuery);
