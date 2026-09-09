/**
 * ADWT — Academic Management System
 * Vanilla JS for AJAX interactions (lecturer/course assignment,
 * department association), UI feedback, and real-time search.
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

// Debounce helper for real-time search
function debounce(func, delay = 300) {
    let timeoutId;
    return function (...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => func(...args), delay);
    };
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

// ── Real-time Search Suggestions ──────────────────────────────────────────────

function initSearchSuggestions() {
    const searchInput = document.getElementById('search-input');
    const suggestionsContainer = document.getElementById('search-suggestions');
    const searchType = document.querySelector('input[name="type"]:checked');

    if (!searchInput || !suggestionsContainer || !searchType) return;

    // Debounced search function
    const performSearch = debounce(async () => {
        const query = searchInput.value.trim();
        
        if (query.length < 2) {
            suggestionsContainer.innerHTML = '';
            suggestionsContainer.classList.add('hidden');
            return;
        }

        const type = document.querySelector('input[name="type"]:checked')?.value || 'all';
        
        try {
            const response = await fetch(
                `/search?q=${encodeURIComponent(query)}&type=${encodeURIComponent(type)}`,
                { headers: { 'Accept': 'application/json' } }
            );

            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            const data = await response.json();
            const students = data.students || [];
            const courses = data.courses || [];

            // Build suggestions HTML
            let html = '';

            if (students.length === 0 && courses.length === 0) {
                html = `
                    <div class="px-4 py-3 text-xs text-gray-500 text-center">
                        No results found for "${escapeHtml(query)}"
                    </div>
                `;
            } else {
                if (students.length > 0) {
                    html += '<div class="border-b border-gray-200">';
                    html += '<div class="px-4 py-2 bg-gray-50 text-xs font-bold text-gray-600 uppercase">Students</div>';
                    students.slice(0, 5).forEach(student => {
                        html += `
                            <a href="/students/${student.id}" class="flex items-center px-4 py-2.5 hover:bg-blue-50 transition-colors border-b border-gray-100 last:border-b-0 group cursor-pointer">
                                <div class="flex-1 min-w-0">
                                    <div class="text-xs font-mono font-bold text-blue-600">${escapeHtml(student.student_id)}</div>
                                    <div class="text-xs text-gray-900 font-medium">${escapeHtml(student.first_name)} ${escapeHtml(student.last_name)}</div>
                                    <div class="text-xs text-gray-500">${escapeHtml(student.email)}</div>
                                </div>
                                <svg class="w-4 h-4 text-gray-300 group-hover:text-blue-400 transition-colors ml-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        `;
                    });
                    if (students.length > 5) {
                        html += `<div class="px-4 py-2 text-xs text-gray-400 text-center bg-gray-50">+${students.length - 5} more students</div>`;
                    }
                    html += '</div>';
                }

                if (courses.length > 0) {
                    html += '<div>';
                    html += '<div class="px-4 py-2 bg-gray-50 text-xs font-bold text-gray-600 uppercase">Courses</div>';
                    courses.slice(0, 5).forEach(course => {
                        html += `
                            <a href="/courses/${course.id}/students" class="flex items-center px-4 py-2.5 hover:bg-emerald-50 transition-colors border-b border-gray-100 last:border-b-0 group cursor-pointer">
                                <div class="flex-1 min-w-0">
                                    <div class="text-xs font-mono font-bold text-emerald-600">${escapeHtml(course.code)}</div>
                                    <div class="text-xs text-gray-900 font-medium">${escapeHtml(course.name)}</div>
                                    <div class="text-xs text-gray-500">${course.credits} credits</div>
                                </div>
                                <svg class="w-4 h-4 text-gray-300 group-hover:text-emerald-400 transition-colors ml-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        `;
                    });
                    if (courses.length > 5) {
                        html += `<div class="px-4 py-2 text-xs text-gray-400 text-center bg-gray-50">+${courses.length - 5} more courses</div>`;
                    }
                    html += '</div>';
                }
            }

            suggestionsContainer.innerHTML = html;
            suggestionsContainer.classList.remove('hidden');
        } catch (error) {
            console.error('Search error:', error);
            suggestionsContainer.innerHTML = `<div class="px-4 py-3 text-xs text-red-500 text-center">Search error</div>`;
            suggestionsContainer.classList.remove('hidden');
        }
    }, 300);

    // Trigger search on input
    searchInput.addEventListener('input', performSearch);

    // Hide suggestions on blur (with delay to allow clicks)
    searchInput.addEventListener('blur', () => {
        setTimeout(() => {
            suggestionsContainer.classList.add('hidden');
        }, 200);
    });

    // Show suggestions on focus
    searchInput.addEventListener('focus', () => {
        if (searchInput.value.trim().length >= 2) {
            suggestionsContainer.classList.remove('hidden');
        }
    });

    // Hide suggestions when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('#search-input') && !e.target.closest('#search-suggestions')) {
            suggestionsContainer.classList.add('hidden');
        }
    });
}

// Helper to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
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
    initSearchSuggestions();
});
