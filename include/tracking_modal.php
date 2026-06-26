<style>
        .glass-panel {
            background: rgba(255,255,255,.8);
            backdrop-filter: blur(12px);
            border:1px solid rgba(255,255,255,.25);
        }
        .dark .glass-panel { background:rgba(15,23,42,.8); border:1px solid rgba(255,255,255,.06); }

        /* ── Timeline ─────────────────────────────── */
        .timeline-wrapper { position:relative; }
        .timeline-line {
            position:absolute; left:19px; top:44px; bottom:0;
            width:2px;
            background:linear-gradient(to bottom, #cbd5e1 0%, #cbd5e1 88%, transparent 100%);
        }
        .dark .timeline-line { background:linear-gradient(to bottom, #334155 0%, #334155 88%, transparent 100%); }

        .tl-node { position:relative; padding-left:56px; padding-bottom:32px; }
        .tl-node:last-child { padding-bottom:0; }

        .tl-dot {
            position:absolute; left:0; top:0;
            width:40px; height:40px; border-radius:50%;
            display:flex; align-items:center; justify-content:center;
            box-shadow:0 0 0 4px #fff, 0 2px 10px rgba(0,0,0,.12);
            z-index:2;
            transition:transform .2s;
        }
        .dark .tl-dot { box-shadow:0 0 0 4px #0f172a, 0 2px 10px rgba(0,0,0,.3); }
        .tl-node:hover .tl-dot { transform:scale(1.08); }

        .tl-card {
            border-radius:14px;
            padding:16px 20px;
            position:relative;
            background:#fff;
            border:1px solid #e2e8f0;
            box-shadow:0 1px 4px rgba(0,0,0,.05);
            transition:box-shadow .2s, transform .2s;
        }
        .dark .tl-card { background:#1e293b; border-color:#334155; }
        .tl-card:hover { box-shadow:0 6px 24px rgba(0,0,0,.09); transform:translateY(-1px); }

        /* Change badge (old → new) */
        .change-badge {
            display:inline-flex; align-items:center; gap:6px;
            padding:3px 10px; border-radius:999px;
            font-size:11px; font-weight:700; font-family:monospace;
            background:rgba(99,102,241,.1); color:#4338ca;
            border:1px solid rgba(99,102,241,.2);
        }
        .dark .change-badge { background:rgba(99,102,241,.15); color:#a5b4fc; border-color:rgba(99,102,241,.3); }

        /* Status progress bar */
        .progress-step { flex:1; text-align:center; position:relative; }
        .progress-step::before {
            content:''; position:absolute; top:14px; left:-50%; right:50%;
            height:2px; background:#e2e8f0; z-index:0;
        }
        .dark .progress-step::before { background:#334155; }
        .progress-step:first-child::before { display:none; }
        .progress-step.active::before,
        .progress-step.done::before { background:#152b4a; }
        .dark .progress-step.active::before,
        .dark .progress-step.done::before { background:#60a5fa; }

        /* Animations */
        @keyframes fadeSlideIn { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }
        .animate-in { animation:fadeSlideIn .35s ease both; }

        /* Overdue pulse */
        @keyframes pulse-red {
            0%,100%{box-shadow:0 0 0 4px #fff,0 2px 10px rgba(0,0,0,.12)}
            50%{box-shadow:0 0 0 6px rgba(239,68,68,.25),0 2px 10px rgba(0,0,0,.12)}
        }
        .overdue-pulse { animation:pulse-red 1.8s infinite; }
        .dark .overdue-pulse {
            animation:none;
            box-shadow:0 0 0 5px rgba(239,68,68,.3),0 2px 10px rgba(0,0,0,.3);
        }

        /* Result row */
        .result-row:hover { background:#f8fafc; }
        .dark .result-row:hover { background:#1e293b; }
    </style>
<div id="trackModal"
     class="fixed inset-0 z-[100] flex items-stretch justify-end pointer-events-none"
     aria-hidden="true">

    <!-- Backdrop -->
    <div id="modalBackdrop"
         onclick="closeTrackModal()"
         class="absolute inset-0 bg-black/50 backdrop-blur-sm opacity-0 transition-opacity duration-300 pointer-events-none"></div>

    <!-- Panel -->
    <div id="modalPanel"
         class="relative w-full max-w-3xl h-full bg-white dark:bg-slate-900 shadow-2xl flex flex-col translate-x-full transition-transform duration-300 ease-[cubic-bezier(.32,.72,0,1)] pointer-events-auto">

        <!-- Panel Header -->
        <div id="modalHeader"
             class="flex-shrink-0 px-6 py-4 bg-gradient-to-r from-navy-700 to-navy-600 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center flex-shrink-0">
                <i data-lucide="route" class="w-5 h-5 text-white"></i>
            </div>
            <div class="flex-1 min-w-0">
                <div id="modalTaskNo" class="text-xs font-bold text-blue-200 tracking-wider font-mono mb-0.5"></div>
                <div id="modalTaskTitle" class="text-base font-bold text-white truncate"></div>
            </div>
            <button onclick="closeTrackModal()"
                    class="p-2 text-white/70 hover:text-white hover:bg-white/10 rounded-lg transition-colors flex-shrink-0">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Task Details Strip -->
        <div id="modalDetailsStrip"
             class="flex-shrink-0 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-6 py-4">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Task ID</p>
                    <p id="mdTaskNo" class="text-sm font-bold text-slate-800 dark:text-white font-mono"></p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Assigned To</p>
                    <p id="mdAssignedTo" class="text-sm font-semibold text-slate-700 dark:text-slate-200 truncate"></p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Priority</p>
                    <p id="mdPriority" class="text-sm font-semibold"></p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Due Date</p>
                    <p id="mdDueDate" class="text-sm font-semibold text-red-600 dark:text-red-400"></p>
                </div>
            </div>
            <!-- Description -->
            <div id="mdDescWrap" class="mt-3 hidden">
                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Description</p>
                <p id="mdDesc" class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed line-clamp-2"></p>
            </div>
        </div>

        <!-- Status Progress Bar -->
        <div id="modalProgressWrap" class="flex-shrink-0 px-6 py-3 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-700">
            <div id="modalProgressBar" class="flex items-start gap-0"></div>
        </div>

        <!-- Scrollable Timeline Content -->
        <div class="flex-1 overflow-y-auto">

            <!-- Loading Spinner -->
            <div id="modalLoader" class="flex flex-col items-center justify-center py-20 gap-4">
                <div class="w-10 h-10 border-4 border-navy-200 border-t-navy-600 rounded-full animate-spin"></div>
                <p class="text-sm text-slate-500 dark:text-slate-400">Loading task journey…</p>
            </div>

            <!-- Error State -->
            <div id="modalError" class="hidden flex-col items-center justify-center py-20 text-center px-8">
                <div class="w-14 h-14 bg-red-50 dark:bg-red-900/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="alert-circle" class="w-7 h-7 text-red-500"></i>
                </div>
                <h3 class="text-base font-semibold text-slate-700 dark:text-slate-200 mb-1">Failed to Load</h3>
                <p id="modalErrorMsg" class="text-sm text-slate-400"></p>
            </div>

            <!-- Timeline -->
            <div id="modalTimeline" class="hidden px-6 py-6">

                <!-- Event count badge -->
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                        <i data-lucide="git-branch" class="w-4 h-4 text-navy-600 dark:text-blue-400"></i>
                        Task Journey Timeline
                    </h3>
                    <span id="modalEventCount" class="text-xs font-semibold px-2.5 py-1 rounded-full bg-navy-50 dark:bg-navy-900/40 text-navy-600 dark:text-blue-400 border border-navy-100 dark:border-navy-800"></span>
                </div>

                <!-- Attachments section (from task_documents) -->
                <div id="modalAttachments" class="hidden mb-5 p-4 rounded-xl border border-blue-100 dark:border-blue-900/40 bg-blue-50 dark:bg-blue-900/10">
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <i data-lucide="paperclip" class="w-3.5 h-3.5"></i> Attached Documents
                    </p>
                    <div id="modalAttachmentList" class="space-y-2"></div>
                </div>

                <!-- Timeline nodes will be injected here -->
                <div id="tlNodes" class="relative">
                    <div class="absolute left-4 top-5 bottom-0 w-0.5 bg-gradient-to-b from-slate-200 to-transparent dark:from-slate-700"></div>
                </div>



            </div>
        </div>

        <!-- Panel Footer -->
        <div class="flex-shrink-0 border-t border-slate-200 dark:border-slate-700 px-6 py-4 bg-white dark:bg-slate-900 flex items-center justify-between gap-3">
            <div id="modalCurrentStatus" class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                <span class="text-xs text-slate-400">Current Status:</span>
                <span id="modalStatusBadge" class="px-2.5 py-1 text-xs font-bold rounded-full"></span>
            </div>
            <div class="flex gap-2">
                <button id="modalFullViewBtn" onclick="goToFullView()"
                   class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold rounded-lg text-white bg-gradient-to-r from-navy-600 to-navy-500 hover:opacity-90 shadow-sm transition-all hover:scale-105 active:scale-95">
                    <i data-lucide="maximize-2" class="w-3.5 h-3.5"></i> Full View
                </button>
                <button onclick="closeTrackModal()"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold rounded-lg text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     FULL-SCREEN TASK VIEW OVERLAY
════════════════════════════════════════════════════════════════ -->
<div id="fullScreenOverlay"
     class="fixed inset-0 z-[200] bg-slate-50 dark:bg-slate-950 flex flex-col"
     style="display:none !important; opacity:0; transition: opacity 0.25s ease;"
     aria-hidden="true">

    <!-- Top Bar -->
    <div class="flex-shrink-0 bg-gradient-to-r from-navy-800 to-navy-600 px-6 py-4 flex items-center gap-4 shadow-lg">
        <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center flex-shrink-0">
            <i data-lucide="maximize-2" class="w-5 h-5 text-white"></i>
        </div>
        <div class="flex-1 min-w-0">
            <div id="fv-task-no" class="text-xs font-bold text-blue-200 tracking-widest font-mono mb-0.5"></div>
            <div id="fv-task-title" class="text-lg font-bold text-white truncate"></div>
        </div>
        <div class="flex items-center gap-3">
            <span id="fv-status-badge" class="px-3 py-1 text-xs font-bold rounded-full"></span>
            <button onclick="closeFullScreen()"
                    class="p-2 text-white/70 hover:text-white hover:bg-white/10 rounded-xl transition-colors flex items-center gap-1.5 text-sm font-semibold">
                <i data-lucide="minimize-2" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Exit Full View</span>
            </button>
        </div>
    </div>

    <!-- Scrollable Body -->
    <div class="flex-1 overflow-y-auto">
        <div class="max-w-5xl mx-auto px-4 sm:px-8 py-8 space-y-6">

            <!-- Task Meta Strip -->
            <div class="glass-panel rounded-2xl shadow-official border border-slate-200/50 dark:border-slate-700/50 p-6">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-5 mb-4">
                    <div>
                        <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Task ID</p>
                        <p id="fv-task-no-detail" class="text-sm font-bold text-navy-600 dark:text-blue-400 font-mono"></p>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Assigned To</p>
                        <p id="fv-assigned" class="text-sm font-semibold text-slate-800 dark:text-white"></p>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Priority</p>
                        <p id="fv-priority" class="text-sm font-bold"></p>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Due Date</p>
                        <p id="fv-due" class="text-sm font-semibold text-red-600 dark:text-red-400"></p>
                    </div>
                </div>
                <div id="fv-desc-wrap" class="hidden">
                    <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Description</p>
                    <p id="fv-desc" class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed bg-slate-50 dark:bg-slate-900/50 rounded-xl p-3 border border-slate-100 dark:border-slate-700"></p>
                </div>
            </div>

            <!-- Status Progress Bar -->
            <div class="glass-panel rounded-2xl shadow-official border border-slate-200/50 dark:border-slate-700/50 p-6">
                <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-5 flex items-center gap-2">
                    <i data-lucide="activity" class="w-3.5 h-3.5"></i> Status Progress
                </h3>
                <div id="fv-progress-bar" class="flex items-start"></div>
            </div>

            <!-- Full Timeline -->
            <div class="glass-panel rounded-2xl shadow-official border border-slate-200/50 dark:border-slate-700/50 overflow-hidden">
                <div class="flex items-center gap-4 px-6 py-5 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-navy-50/60 via-white to-white dark:from-slate-900/60 dark:via-slate-800 dark:to-slate-800">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-navy-600 to-navy-500 flex items-center justify-center shadow-md flex-shrink-0">
                        <i data-lucide="git-branch" class="w-5 h-5 text-white"></i>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Task Journey Timeline</h2>
                        <p id="fv-event-count" class="text-xs text-slate-500 dark:text-slate-400 mt-0.5"></p>
                    </div>
                </div>
                <div class="p-6 sm:p-8">
                    <!-- Attachments -->
                    <div id="fv-attachments" class="hidden mb-6 p-4 rounded-xl border border-blue-100 dark:border-blue-900/40 bg-blue-50 dark:bg-blue-900/10">
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                            <i data-lucide="paperclip" class="w-3.5 h-3.5"></i> Attached Documents
                        </p>
                        <div id="fv-attachment-list" class="space-y-2"></div>
                    </div>
                    <!-- Timeline nodes -->
                    <div id="fv-timeline-nodes" class="relative">
                        <div class="absolute left-5 top-5 bottom-0 w-0.5 bg-gradient-to-b from-slate-200 via-slate-200 to-transparent dark:from-slate-700 dark:via-slate-700"></div>
                    </div>
                </div>
            </div>

            <!-- Footer spacing -->
            <div class="h-6"></div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     MODAL JAVASCRIPT
════════════════════════════════════════════════════════════════ -->
<script>
// ── Config ────────────────────────────────────────────────────────
const AJAX_BASE = 'task_tracking.php?ajax=timeline&task_id=';

// ── Icon map by event_type ────────────────────────────────────────
const TL_ICONS = {
    created:      { icon:'plus-circle',   bg:'from-slate-500 to-slate-600',  col:'#64748b', label:'Created'     },
    assigned:     { icon:'user-check',    bg:'from-blue-500 to-blue-600',    col:'#3b82f6', label:'Assigned'    },
    acknowledged: { icon:'thumbs-up',     bg:'from-cyan-500 to-cyan-600',    col:'#06b6d4', label:'Acknowledged'},
    started:      { icon:'play-circle',   bg:'from-indigo-500 to-indigo-600',col:'#6366f1', label:'Started'     },
    status_change:{ icon:'refresh-cw',    bg:'from-purple-500 to-purple-600',col:'#8b5cf6', label:'Changed'     },
    tracking:     { icon:'git-commit',    bg:'from-slate-400 to-slate-500',  col:'#94a3b8', label:'Admin Log'   },
    remark:       { icon:'message-square',bg:'from-amber-400 to-amber-500',  col:'#f59e0b', label:'Remark'      },
    rejected:     { icon:'x-circle',      bg:'from-red-500 to-red-600',      col:'#ef4444', label:'Rejected'    },
    completed:    { icon:'check-circle-2',bg:'from-green-500 to-green-600',  col:'#22c55e', label:'Completed'   },
    escalation:   { icon:'alert-triangle',bg:'from-red-600 to-red-700',      col:'#ef4444', label:'Escalated'   },
    overdue:      { icon:'alert-triangle',bg:'from-red-600 to-red-700',      col:'#ef4444', label:'Overdue'     },
    document:     { icon:'paperclip',     bg:'from-sky-400 to-sky-500',      col:'#0ea5e9', label:'Document'    },
    activity:     { icon:'zap',           bg:'from-slate-400 to-slate-500',  col:'#94a3b8', label:'Activity'    },
};

const STATUS_COLORS = {
    pending:     'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
    assigned:    'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
    'in progress':'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
    'on hold':   'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
    completed:   'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
    rejected:    'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
    cancelled:   'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
    overdue:     'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
    escalated:   'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
};

function getStatusClass(s) {
    return STATUS_COLORS[(s||'').toLowerCase()] || 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300';
}

function getProgressStyle(sl) {
    if (sl.includes('pending'))   return { bg: 'bg-yellow-500', text: 'text-yellow-600 dark:text-yellow-400', icon: 'clock' };
    if (sl.includes('assign'))    return { bg: 'bg-blue-500',   text: 'text-blue-600 dark:text-blue-400',   icon: 'user-check' };
    if (sl.includes('progress'))  return { bg: 'bg-purple-500', text: 'text-purple-600 dark:text-purple-400', icon: 'loader' };
    if (sl.includes('complet'))   return { bg: 'bg-green-500',  text: 'text-green-600 dark:text-green-400', icon: 'check-circle-2' };
    if (sl.includes('reject') || sl.includes('cancel')) return { bg: 'bg-red-500', text: 'text-red-600 dark:text-red-400', icon: 'x-circle' };
    if (sl.includes('overdue') || sl.includes('escalate')) return { bg: 'bg-red-500', text: 'text-red-600 dark:text-red-400', icon: 'alert-triangle' };
    return { bg: 'bg-slate-500', text: 'text-slate-600 dark:text-slate-400', icon: 'circle' };
}

function getTlInfo(et) {
    return TL_ICONS[et] || TL_ICONS['activity'];
}

function fmtDate(d) {
    if (!d) return '';
    const dt = new Date(d);
    if (isNaN(dt)) return d;
    return dt.toLocaleDateString('en-IN', {day:'2-digit',month:'short',year:'numeric'})
         + ' · ' + dt.toLocaleTimeString('en-IN', {hour:'2-digit',minute:'2-digit'});
}

function fmtDateShort(d) {
    if (!d) return '—';
    const dt = new Date(d);
    if (isNaN(dt)) return d;
    return dt.toLocaleDateString('en-IN', {day:'2-digit',month:'short',year:'numeric'});
}

// ── Open Modal ────────────────────────────────────────────────────
async function openTrackModal(taskId) {
    const modal    = document.getElementById('trackModal');
    const backdrop = document.getElementById('modalBackdrop');
    const panel    = document.getElementById('modalPanel');
    const loader   = document.getElementById('modalLoader');
    const err      = document.getElementById('modalError');
    const timeline = document.getElementById('modalTimeline');

    // Reset state
    loader.style.display = 'flex';
    err.classList.add('hidden');
    timeline.classList.add('hidden');
    document.getElementById('tlNodes').innerHTML =
        '<div class="absolute left-4 top-5 bottom-0 w-0.5 bg-gradient-to-b from-slate-200 to-transparent dark:from-slate-700"></div>';
    document.getElementById('modalTaskNo').textContent    = '';
    document.getElementById('modalTaskTitle').textContent = '';
    document.getElementById('modalProgressBar').innerHTML = '';

    // Show modal
    modal.style.pointerEvents = 'auto';
    modal.setAttribute('aria-hidden', 'false');
    backdrop.style.pointerEvents = 'auto';
    document.body.style.overflow = 'hidden';

    requestAnimationFrame(() => {
        backdrop.style.opacity = '1';
        panel.style.transform  = 'translateX(0)';
    });

    // Fetch data
    try {
        const res  = await fetch(AJAX_BASE + taskId);
        const data = await res.json();

        if (data.error) throw new Error(data.error);

        renderModal(data.task, data.events, data.docs || []);
        // Cache data for Full View
        _fvCache = { task: data.task, events: data.events, docs: data.docs || [] };
    } catch(e) {
        loader.style.display = 'none';
        err.classList.remove('hidden');
        err.style.display = 'flex';
        document.getElementById('modalErrorMsg').textContent = e.message || 'Could not load task data.';
    }

    lucide.createIcons();
}

// ── Global cache for task data (used by Full View) ───────────────
let _fvCache = { task: null, events: [], docs: [] };

// ── Navigate to Full View (full-screen overlay) ───────────────────
function goToFullView() {
    if (!_fvCache.task) return;
    openFullScreen(_fvCache.task, _fvCache.events, _fvCache.docs);
}

// ── Open Full-Screen Overlay ──────────────────────────────────────
function openFullScreen(task, events, docs) {
    const overlay = document.getElementById('fullScreenOverlay');
    if (!overlay) return;

    // Populate header
    const taskNo = task.task_no || ('#' + task.task_id);
    document.getElementById('fv-task-no').textContent       = taskNo;
    document.getElementById('fv-task-title').textContent    = task.task_title || '—';
    document.getElementById('fv-task-no-detail').textContent= taskNo;
    document.getElementById('fv-assigned').textContent      = task.assigned_employee || task.role_name || 'Unassigned';
    document.getElementById('fv-due').textContent           = fmtDateShort(task.due_date);

    // Priority
    const pEl = document.getElementById('fv-priority');
    pEl.textContent = task.priority || '—';
    const pMap = { high:'text-red-600', medium:'text-yellow-600', low:'text-green-600', critical:'text-purple-600' };
    pEl.className = 'text-sm font-bold ' + (pMap[(task.priority||'').toLowerCase()] || 'text-slate-600 dark:text-slate-300');

    // Description
    const descWrap = document.getElementById('fv-desc-wrap');
    const descEl   = document.getElementById('fv-desc');
    if (task.task_description) {
        descEl.textContent = task.task_description;
        descWrap.classList.remove('hidden');
    } else {
        descWrap.classList.add('hidden');
    }

    // Status badge
    const status = task.status || 'Pending';
    const sb = document.getElementById('fv-status-badge');
    sb.textContent = status;
    sb.className = 'px-3 py-1 text-xs font-bold rounded-full ' + getStatusClass(status);

    // Progress bar
    const st = status.toLowerCase();
    let flow = ['Pending', 'Assigned', 'In Progress', 'Completed'];
    if (st === 'rejected')  flow = ['Pending', 'Assigned', 'In Progress', 'Rejected'];
    if (st === 'on hold')   flow = ['Pending', 'Assigned', 'In Progress', 'On Hold', 'Completed'];
    if (st === 'escalated') flow = ['Pending', 'Assigned', 'In Progress', 'Escalated'];
    if (!flow.map(s => s.toLowerCase()).includes(st)) flow.push(status);
    const curIdx = flow.findIndex(s => s.toLowerCase() === st);
    document.getElementById('fv-progress-bar').innerHTML = flow.map((s, idx) => {
        const done = idx < curIdx, active = idx === curIdx;
        const sl = s.toLowerCase();
        let style = getProgressStyle(sl);
        let dotBg = 'bg-slate-200 dark:bg-slate-700';
        let lbl   = 'text-slate-400 text-[11px]';
        let lineC = 'bg-slate-200 dark:bg-slate-700';
        
        if (idx <= curIdx) {
            dotBg = style.bg;
            lbl   = style.text + ' font-semibold text-[11px]';
            lineC = style.bg;
        }
        const line  = idx > 0 ? `<div class="absolute top-4 right-1/2 left-0 h-0.5 ${lineC}"></div>` : '';
        const inner = (done || active)
                    ? `<i data-lucide="${style.icon}" class="w-4 h-4 text-white"></i>`
                    : `<div class="w-2 h-2 rounded-full bg-slate-400 dark:bg-slate-500"></div>`;
        return `<div class="flex-1 text-center relative">
            ${line}
            <div class="flex justify-center mb-2">
                <div class="relative z-10 w-8 h-8 rounded-full ${dotBg} flex items-center justify-center shadow-sm">${inner}</div>
            </div>
            <div class="${lbl} leading-tight">${s}</div>
        </div>`;
    }).join('');

    // Attachments
    const attEl   = document.getElementById('fv-attachments');
    const attList = document.getElementById('fv-attachment-list');
    if (docs && docs.length > 0) {
        attList.innerHTML = docs.map(doc => {
            const name = doc.original_name || (doc.file_path ? doc.file_path.split('/').pop() : 'Document');
            const ext  = name.split('.').pop().toUpperCase();
            const size = doc.file_size ? (doc.file_size / 1024).toFixed(1) + ' KB' : '';
            return `<div class="flex items-center gap-3 p-2.5 bg-white dark:bg-slate-800 rounded-xl border border-blue-100 dark:border-blue-900/50">
                <div class="w-9 h-9 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i data-lucide="file" class="w-4 h-4 text-blue-600 dark:text-blue-400"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-slate-800 dark:text-white truncate">${escHtml(name)}</p>
                    <p class="text-[10px] text-slate-400">${ext}${size ? ' · ' + size : ''}${doc.uploaded_by_name ? ' · ' + escHtml(doc.uploaded_by_name) : ''}</p>
                </div>
                <a href="${escHtml(doc.file_path)}" target="_blank" rel="noopener"
                   class="px-2.5 py-1.5 text-[10px] font-semibold rounded-lg text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 transition-colors flex-shrink-0">
                    <i data-lucide="download" class="w-3.5 h-3.5 inline-block"></i> Download
                </a>
            </div>`;
        }).join('');
        attEl.classList.remove('hidden');
    } else {
        attEl.classList.add('hidden');
    }

    // Event count
    document.getElementById('fv-event-count').textContent =
        events.length + ' event' + (events.length !== 1 ? 's' : '') + ' · Chronological order';

    // Timeline nodes
    const tlContainer = document.getElementById('fv-timeline-nodes');
    tlContainer.innerHTML = '<div class="absolute left-5 top-5 bottom-0 w-0.5 bg-gradient-to-b from-slate-200 via-slate-200 to-transparent dark:from-slate-700 dark:via-slate-700"></div>';
    if (events.length === 0) {
        tlContainer.innerHTML += `<div class="text-center py-16 text-sm text-slate-400">No timeline events recorded yet.</div>`;
    } else {
        tlContainer.innerHTML += events.map((ev, i) => buildFvTimelineNode(ev, i, events.length)).join('');
    }

    // Show overlay
    overlay.style.removeProperty('display');
    overlay.style.display = 'flex';
    overlay.style.flexDirection = 'column';
    document.body.style.overflow = 'hidden';
    overlay.setAttribute('aria-hidden', 'false');
    requestAnimationFrame(() => { overlay.style.opacity = '1'; });
    lucide.createIcons();
}

// ── Close Full-Screen Overlay ──────────────────────────────────────
function closeFullScreen() {
    const overlay = document.getElementById('fullScreenOverlay');
    if (!overlay) return;
    overlay.style.opacity = '0';
    document.body.style.overflow = 'hidden'; // keep drawer scroll locked
    setTimeout(() => {
        overlay.style.display = 'none';
        overlay.setAttribute('aria-hidden', 'true');
    }, 250);
}

// ── Build Full-View timeline node (larger cards) ──────────────────
function buildFvTimelineNode(ev, idx, total) {
    const info      = getTlInfo(ev.event_type);
    const isLatest  = idx === total - 1;
    const isOverdue = ev.event_type === 'overdue';
    const isRejected   = ev.event_type === 'rejected';
    const isCompleted  = ev.event_type === 'completed';

    let changeBadge = '';
    if (ev.old_status || ev.new_status) {
        const oldS = ev.old_status || '—';
        const newS = ev.new_status || '—';
        changeBadge = `<div class="flex items-center gap-2 mb-3">
            <span class="text-[11px] font-mono font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">${escHtml(oldS)}</span>
            <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-slate-400 flex-shrink-0"></i>
            <span class="text-[11px] font-mono font-semibold px-2.5 py-1 rounded-full ${getStatusClass(newS)}">${escHtml(newS)}</span>
        </div>`;
    }

    let specialSection = '';
    if (isRejected && ev.remarks) {
        specialSection = `<div class="mt-3 p-3 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-900/40">
            <p class="text-[11px] font-semibold text-red-600 dark:text-red-400 uppercase tracking-wider mb-1">Rejection Reason</p>
            <p class="text-sm text-red-700 dark:text-red-300">${escHtml(ev.remarks)}</p>
        </div>`;
    } else if (isCompleted && ev.remarks) {
        specialSection = `<div class="mt-3 p-3 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-100 dark:border-green-900/40">
            <p class="text-[11px] font-semibold text-green-600 dark:text-green-400 uppercase tracking-wider mb-1">Completion Details</p>
            <p class="text-sm text-green-700 dark:text-green-300">${escHtml(ev.remarks)}</p>
        </div>`;
    }

    let remarksSection = '';
    if (!isRejected && !isCompleted && ev.remarks && ev.remarks !== ev.description) {
        remarksSection = `<div class="mt-3 p-3 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-900/30">
            <p class="text-[11px] font-semibold text-amber-600 dark:text-amber-400 mb-1">Remarks</p>
            <p class="text-sm text-amber-800 dark:text-amber-300">${escHtml(ev.remarks)}</p>
        </div>`;
    }

    const overduePulse = isOverdue ? ' ring-4 ring-red-300 dark:ring-red-900' : '';
    const latestBadge  = isLatest  ? `<span class="inline-flex items-center gap-1 px-2 py-0.5 text-[9px] font-bold rounded-full bg-navy-600 text-white ml-2">LATEST</span>` : '';

    return `<div class="relative pl-16 pb-8" style="animation: fadeSlideIn 0.3s ease ${(idx * 0.04).toFixed(2)}s both">
        <div class="absolute left-0 top-0 w-10 h-10 rounded-full bg-gradient-to-br ${info.bg} flex items-center justify-center z-10 shadow-md${overduePulse}">
            <i data-lucide="${info.icon}" class="w-[18px] h-[18px] text-white"></i>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-2xl border-l-4 border border-slate-200 dark:border-slate-700 p-5 shadow-sm hover:shadow-md transition-shadow"
             style="border-left-color: ${info.col}">
            <div class="absolute -top-2.5 left-18" style="left:4.5rem">
                <span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-400 shadow-sm">${escHtml(info.label)}</span>
            </div>
            <div class="flex items-start justify-between gap-2 mb-2 mt-1">
                <h4 class="text-base font-bold text-slate-900 dark:text-white leading-tight">
                    ${escHtml(ev.title)}${latestBadge}
                </h4>
                ${isOverdue ? '<span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800 animate-pulse flex-shrink-0">⚠ OVERDUE</span>' : ''}
            </div>
            ${changeBadge}
            ${ev.description ? `<p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed mb-3">${escHtml(ev.description)}</p>` : ''}
            ${specialSection}
            ${remarksSection}
            <div class="flex flex-wrap items-center gap-x-5 gap-y-1.5 mt-4 pt-3 border-t border-slate-100 dark:border-slate-700/60">
                ${ev.actor_name ? `<div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-600 flex items-center justify-center text-[10px] font-bold text-slate-600 dark:text-white flex-shrink-0">${escHtml((ev.actor_name||'S').charAt(0).toUpperCase())}</div>
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">${escHtml(ev.actor_name)}</span>
                    ${ev.actor_desig ? `<span class="text-xs text-slate-400">· ${escHtml(ev.actor_desig)}</span>` : ''}
                </div>` : ''}
                ${ev.event_date ? `<div class="flex items-center gap-1.5 text-xs text-slate-400 dark:text-slate-500 font-mono ml-auto">
                    <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                    ${fmtDate(ev.event_date)}
                </div>` : ''}
            </div>
        </div>
    </div>`;
}

// ── Close Modal ───────────────────────────────────────────────────
function closeTrackModal() {
    const modal    = document.getElementById('trackModal');
    const backdrop = document.getElementById('modalBackdrop');
    const panel    = document.getElementById('modalPanel');

    backdrop.style.opacity = '0';
    panel.style.transform  = 'translateX(100%)';
    document.body.style.overflow = '';

    setTimeout(() => {
        modal.style.pointerEvents = 'none';
        backdrop.style.pointerEvents = 'none';
        modal.setAttribute('aria-hidden', 'true');
    }, 300);
}

// Close on Escape
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        const fv = document.getElementById('fullScreenOverlay');
        if (fv && fv.style.display !== 'none' && fv.getAttribute('aria-hidden') !== 'true') {
            closeFullScreen();
        } else {
            closeTrackModal();
        }
    }
});

// ── Render Modal Content ──────────────────────────────────────────
function renderModal(task, events, docs) {
    const loader   = document.getElementById('modalLoader');
    const timeline = document.getElementById('modalTimeline');
    loader.style.display = 'none';



    // Header
    const taskNo = task.task_no || ('#' + task.task_id);
    document.getElementById('modalTaskNo').textContent    = taskNo;
    document.getElementById('modalTaskTitle').textContent = task.task_title || '—';

    // Details strip
    document.getElementById('mdTaskNo').textContent     = taskNo;
    document.getElementById('mdAssignedTo').textContent = task.assigned_employee || task.role_name || 'Unassigned';
    const pEl = document.getElementById('mdPriority');
    pEl.textContent = task.priority || '—';
    const pMap = { high:'text-red-600 dark:text-red-400', medium:'text-yellow-600 dark:text-yellow-400', low:'text-green-600 dark:text-green-400', critical:'text-purple-600 dark:text-purple-400' };
    pEl.className = 'text-sm font-bold ' + (pMap[(task.priority||'').toLowerCase()] || 'text-slate-600 dark:text-slate-300');
    document.getElementById('mdDueDate').textContent    = fmtDateShort(task.due_date);

    const descEl = document.getElementById('mdDesc');
    const descWrap = document.getElementById('mdDescWrap');
    if (task.task_description) {
        descEl.textContent = task.task_description;
        descWrap.classList.remove('hidden');
    }

    // Footer badge
    const status   = task.status || 'Pending';
    const statusBadge = document.getElementById('modalStatusBadge');
    statusBadge.textContent = status;
    statusBadge.className   = 'px-2.5 py-1 text-xs font-bold rounded-full ' + getStatusClass(status);

    // Store current task_id on panel for Full View navigation
    document.getElementById('modalPanel').dataset.taskId = task.task_id;

    // ── Status Progress Bar ───────────────────────────────────────
    const st = (status).toLowerCase();
    let flow = ['Pending', 'Assigned', 'In Progress', 'Completed'];
    if (st === 'rejected')  flow = ['Pending', 'Assigned', 'In Progress', 'Rejected'];
    if (st === 'on hold')   flow = ['Pending', 'Assigned', 'In Progress', 'On Hold', 'Completed'];
    if (st === 'escalated') flow = ['Pending', 'Assigned', 'In Progress', 'Escalated'];
    if (!flow.map(s=>s.toLowerCase()).includes(st)) flow.push(status);

    const curIdx = flow.findIndex(s => s.toLowerCase() === st);
    const pbEl   = document.getElementById('modalProgressBar');
    pbEl.innerHTML = flow.map((s, idx) => {
        const done   = idx < curIdx;
        const active = idx === curIdx;
        const sl     = s.toLowerCase();
        let style = getProgressStyle(sl);
        let dotBg = 'bg-slate-200 dark:bg-slate-700';
        let lbl   = 'text-slate-400 text-[10px]';
        let lineC = 'bg-slate-200 dark:bg-slate-700';
        
        if (idx <= curIdx) {
            dotBg = style.bg;
            lbl   = style.text + ' font-semibold text-[10px]';
            lineC = style.bg;
        }
        const line = idx > 0 ? `<div class="absolute top-3.5 right-1/2 left-0 h-0.5 ${lineC}"></div>` : '';
        const inner = (done || active)
                    ? `<i data-lucide="${style.icon}" class="w-3.5 h-3.5 text-white"></i>`
                    : `<div class="w-1.5 h-1.5 rounded-full bg-slate-400 dark:bg-slate-500"></div>`;
        return `<div class="flex-1 text-center relative">
                    ${line}
                    <div class="flex justify-center mb-1.5">
                        <div class="relative z-10 w-7 h-7 rounded-full ${dotBg} flex items-center justify-center shadow-sm">${inner}</div>
                    </div>
                    <div class="${lbl} leading-tight">${s}</div>
                </div>`;
    }).join('');

    // ── Attachments ───────────────────────────────────────────────
    const attEl   = document.getElementById('modalAttachments');
    const attList = document.getElementById('modalAttachmentList');
    if (docs && docs.length > 0) {
        attList.innerHTML = docs.map(doc => {
            const name = doc.original_name || (doc.file_path ? doc.file_path.split('/').pop() : 'Document');
            const ext  = name.split('.').pop().toUpperCase();
            const size = doc.file_size ? (doc.file_size / 1024).toFixed(1) + ' KB' : '';
            return `<div class="flex items-center gap-3 p-2 bg-white dark:bg-slate-800 rounded-lg border border-blue-100 dark:border-blue-900/50">
                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-md flex items-center justify-center flex-shrink-0">
                            <i data-lucide="file" class="w-4 h-4 text-blue-600 dark:text-blue-400"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-slate-800 dark:text-white truncate">${escHtml(name)}</p>
                            <p class="text-[10px] text-slate-400">${ext}${size ? ' · ' + size : ''}${doc.uploaded_by_name ? ' · ' + escHtml(doc.uploaded_by_name) : ''}</p>
                        </div>
                        <a href="${escHtml(doc.file_path)}" target="_blank" rel="noopener"
                           class="px-2 py-1 text-[10px] font-semibold rounded text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 transition-colors flex-shrink-0">
                            <i data-lucide="download" class="w-3 h-3 inline-block"></i>
                        </a>
                    </div>`;
        }).join('');
        attEl.classList.remove('hidden');
    }

    // ── Timeline Nodes ────────────────────────────────────────────
    document.getElementById('modalEventCount').textContent = events.length + ' event' + (events.length !== 1 ? 's' : '');

    const tlContainer = document.getElementById('tlNodes');
    if (events.length === 0) {
        tlContainer.innerHTML = `<div class="text-center py-10 text-sm text-slate-400">No timeline events recorded yet.</div>`;
    } else {
        const nodes = events.map((ev, i) => buildTimelineNode(ev, i, events.length)).join('');
        tlContainer.innerHTML =
            '<div class="absolute left-4 top-5 bottom-0 w-0.5 bg-gradient-to-b from-slate-200 via-slate-200 to-transparent dark:from-slate-700 dark:via-slate-700"></div>'
            + nodes;
    }

    timeline.classList.remove('hidden');
    lucide.createIcons();
}

// ── Build one timeline node ───────────────────────────────────────
function buildTimelineNode(ev, idx, total) {
    const info     = getTlInfo(ev.event_type);
    const isLatest = idx === total - 1;
    const isOverdue = ev.event_type === 'overdue';
    const isRejected = ev.event_type === 'rejected';
    const isCompleted = ev.event_type === 'completed';

    // Change badge (old → new)
    let changeBadge = '';
    if (ev.old_status || ev.new_status) {
        const oldS = ev.old_status || '—';
        const newS = ev.new_status || '—';
        changeBadge = `
            <div class="flex items-center gap-1.5 mb-2">
                <span class="text-[10px] font-mono font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">${escHtml(oldS)}</span>
                <i data-lucide="arrow-right" class="w-3 h-3 text-slate-400 flex-shrink-0"></i>
                <span class="text-[10px] font-mono font-semibold px-2 py-0.5 rounded-full ${getStatusClass(newS)}">${escHtml(newS)}</span>
            </div>`;
    }

    // Special section for rejected: show reason prominently
    let specialSection = '';
    if (isRejected && ev.remarks) {
        specialSection = `
            <div class="mt-2 p-2.5 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-900/40">
                <p class="text-[10px] font-semibold text-red-600 dark:text-red-400 uppercase tracking-wider mb-1">Rejection Reason</p>
                <p class="text-xs text-red-700 dark:text-red-300">${escHtml(ev.remarks)}</p>
            </div>`;
    }

    // Achievement/completion section
    if (isCompleted && ev.remarks) {
        specialSection = `
            <div class="mt-2 p-2.5 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-100 dark:border-green-900/40">
                <p class="text-[10px] font-semibold text-green-600 dark:text-green-400 uppercase tracking-wider mb-1">Completion Details</p>
                <p class="text-xs text-green-700 dark:text-green-300">${escHtml(ev.remarks)}</p>
            </div>`;
    }

    // Remarks section (for other types)
    let remarksSection = '';
    if (!isRejected && !isCompleted && ev.remarks && ev.remarks !== ev.description) {
        remarksSection = `
            <div class="mt-2 p-2 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-900/30">
                <p class="text-[10px] font-semibold text-amber-600 dark:text-amber-400 mb-0.5">Remarks</p>
                <p class="text-xs text-amber-800 dark:text-amber-300">${escHtml(ev.remarks)}</p>
            </div>`;
    }

    const overduePulse = isOverdue ? ' ring-4 ring-red-300 dark:ring-red-900' : '';
    const latestBadge = isLatest ? `<span class="inline-flex items-center gap-1 px-1.5 py-0.5 text-[9px] font-bold rounded-full bg-navy-600 text-white ml-2">LATEST</span>` : '';

    return `
        <div class="relative pl-12 pb-7 tl-modal-node" style="animation: fadeSlideIn 0.3s ease ${(idx * 0.04).toFixed(2)}s both">
            <!-- Icon dot -->
            <div class="absolute left-0 top-0 w-8 h-8 rounded-full bg-gradient-to-br ${info.bg} flex items-center justify-center z-10 shadow-sm${overduePulse}">
                <i data-lucide="${info.icon}" class="w-[15px] h-[15px] text-white"></i>
            </div>

            <!-- Card -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border-l-4 border border-slate-200 dark:border-slate-700 p-4 shadow-sm hover:shadow-md transition-shadow"
                 style="border-left-color: ${info.col}">

                <!-- Event type label -->
                <div class="absolute -top-2 left-14">
                    <span class="text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-400 shadow-xs">
                        ${escHtml(info.label)}
                    </span>
                </div>

                <!-- Title + Latest badge -->
                <div class="flex items-start justify-between gap-2 mb-2 mt-1">
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white leading-tight">
                        ${escHtml(ev.title)}${latestBadge}
                    </h4>
                    ${isOverdue ? '<span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800 animate-pulse flex-shrink-0">⚠ OVERDUE</span>' : ''}
                </div>

                <!-- Old → New status change -->
                ${changeBadge}

                <!-- Description -->
                ${ev.description ? `<p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed mb-2">${escHtml(ev.description)}</p>` : ''}

                <!-- Special sections (rejection/completion) -->
                ${specialSection}
                ${remarksSection}

                <!-- Meta: Actor + Date -->
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-3 pt-2 border-t border-slate-100 dark:border-slate-700/60">
                    ${ev.actor_name ? `
                    <div class="flex items-center gap-1.5">
                        <div class="w-5 h-5 rounded-full bg-slate-200 dark:bg-slate-600 flex items-center justify-center text-[9px] font-bold text-slate-600 dark:text-white flex-shrink-0">
                            ${escHtml((ev.actor_name || 'S').charAt(0).toUpperCase())}
                        </div>
                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-200">${escHtml(ev.actor_name)}</span>
                        ${ev.actor_desig ? `<span class="text-[10px] text-slate-400">· ${escHtml(ev.actor_desig)}</span>` : ''}
                    </div>` : ''}
                    ${ev.event_date ? `
                    <div class="flex items-center gap-1 text-[10px] text-slate-400 dark:text-slate-500 font-mono ml-auto">
                        <i data-lucide="clock" class="w-3 h-3"></i>
                        ${fmtDate(ev.event_date)}
                    </div>` : ''}
                </div>
            </div>
        </div>`;
}

// ── HTML escape helper ────────────────────────────────────────────
function escHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
</script>