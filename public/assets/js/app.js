/**
 * ADWT — Academic Management System
 * Vanilla JS for AJAX interactions (lecturer/course assignment,
 * department association) and UI feedback.
 */

// ── Utility ───────────────────────────────────────────────────────────────────

function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = [
        'fixed bottom-6 right-6 px-5 py-3 rounded-lg shadow-lg text-white text-sm font-medium',
        'transition-opacity duration-300 opacity-100 z-50',
        type === 'success' ? 'bg-green-600' : 'bg-red-600',
    ].join(' ');
    toast.textContent = message;
    container.appendChild(toast);

    setTimeout(() => {
        toast.classList.replace('opacity-100', 'opacity-0');
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}

async function postForm(url, data) {
    const body = new URLSearchParams(data);
    const res  = await fetch(url, {
        method:  'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body,
    });
    return res.json();
}

// ── Lecturer assignment on Course detail/edit ─────────────────────────────────

function initAssignLecturer() {
    const btn = document.getElementById('btn-assign-lecturer');
    if (!btn) return;

    btn.addEventListener('click', async () => {
        const select    = document.getElementById('select-lecturer');
        const courseId  = btn.dataset.courseId;
        const lecturerId = select.value;
        if (!lecturerId) return;

        const result = await postForm(`/courses/${courseId}/assign-lecturer`, {
            lecturer_id: lecturerId,
        });
        showToast(result.message, result.success ? 'success' : 'error');
        if (result.success) setTimeout(() => location.reload(), 800);
    });

    // Remove lecturer buttons
    document.querySelectorAll('.btn-remove-lecturer').forEach(btn => {
        btn.addEventListener('click', async () => {
            const courseId   = btn.dataset.courseId;
            const lecturerId = btn.dataset.lecturerId;

            const result = await postForm(`/courses/${courseId}/remove-lecturer`, {
                _method:     'DELETE',
                lecturer_id: lecturerId,
            });
            showToast(result.message, result.success ? 'success' : 'error');
            if (result.success) setTimeout(() => location.reload(), 800);
        });
    });
}

// ── Department association on Lecturer edit ───────────────────────────────────

function initAssociateDepartment() {
    const btn = document.getElementById('btn-associate-dept');
    if (!btn) return;

    btn.addEventListener('click', async () => {
        const select       = document.getElementById('select-department');
        const lecturerId   = btn.dataset.lecturerId;
        const departmentId = select.value;

        const result = await postForm(`/lecturers/${lecturerId}/associate-department`, {
            department_id: departmentId,
        });
        showToast(result.message, result.success ? 'success' : 'error');
        if (result.success) setTimeout(() => location.reload(), 800);
    });
}

// ── Init ──────────────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', () => {
    initAssignLecturer();
    initAssociateDepartment();
});
