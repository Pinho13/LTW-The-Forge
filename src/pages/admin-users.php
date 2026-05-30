<?php
declare(strict_types=1);
require_once(__DIR__ . '/../utils/page_bootstrap.php');
require_once(__DIR__ . '/../../database/models/AdminUser.class.php');

[$session, $db] = requireAuthenticatedPage();

if (!$session->isAdmin()) {
    $session->addMessage('error', 'Access denied.');
    header('Location: /src/pages/index.php');
    exit;
}

$filterRole         = $_GET['role']         ?? '';
$filterSearch       = trim($_GET['q']       ?? '');
$filterStatus       = $_GET['status']       ?? 'all';
$filterJoined       = $_GET['joined']       ?? '';
$filterSubscription = $_GET['subscription'] ?? '';

if (!in_array($filterRole,         ['', 'member', 'trainer', 'admin']))      $filterRole = '';
if (!in_array($filterStatus,       ['active', 'banned', 'frozen', 'all']))   $filterStatus = 'active';
if (!in_array($filterJoined,       ['', 'week', 'month', 'year']))           $filterJoined = '';
if (!in_array($filterSubscription, ['', 'expired']))                         $filterSubscription = '';

$users = AdminUser::getAll($db, $filterRole, $filterSearch, $filterStatus, $session->getId(), $filterJoined, $filterSubscription);

$csrf = $session->getCsrfToken();
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - The Forge Admin</title>
    <link rel="stylesheet" href="../style/main.css">
    <link rel="stylesheet" href="../style/layout.css">
    <link rel="stylesheet" href="../style/admin-users.css">
</head>

<body>
    <?php $activePage = 'admin-users'; include '../components/side-menu.php'; ?>

    <main>
        <?php include '../components/flash-messages.php'; ?>

        <header>
            <h1>Users</h1>
            <div class="users-header-actions">
                <button type="button" class="users-header-btn" id="users-export-btn">Export</button>
            </div>
        </header>

        <?php if ($filterSubscription === 'expired'): ?>
        <div class="filter-banner">
            <span>Showing members with expired subscriptions still marked active.</span>
            <a href="/src/pages/admin-users.php" class="filter-banner__clear">Clear filter</a>
        </div>
        <?php endif; ?>

        <form class="user-filters" method="GET" action="" id="user-filters-form">
            <?php if ($filterSubscription !== ''): ?>
            <input type="hidden" name="subscription" value="<?= htmlspecialchars($filterSubscription) ?>">
            <?php endif; ?>
            <input type="text" name="q" placeholder="Search name, username, email…"
                   value="<?= htmlspecialchars($filterSearch) ?>" class="user-filters__search"
                   id="search-input" oninput="liveSearch(this.value)" autocomplete="off">

            <div class="user-filters__group">
                <label class="user-filters__label" for="filter-role">Role</label>
                <select id="filter-role" name="role" class="user-filters__select" onchange="this.form.submit()">
                    <option value=""       <?= $filterRole === ''        ? 'selected' : '' ?>>All</option>
                    <option value="member"  <?= $filterRole === 'member'  ? 'selected' : '' ?>>Members</option>
                    <option value="trainer" <?= $filterRole === 'trainer' ? 'selected' : '' ?>>Trainers</option>
                    <option value="admin"   <?= $filterRole === 'admin'   ? 'selected' : '' ?>>Admins</option>
                </select>
            </div>

            <div class="user-filters__group">
                <label class="user-filters__label" for="filter-status">Status</label>
                <select id="filter-status" name="status" class="user-filters__select" onchange="this.form.submit()">
                    <option value="all"    <?= $filterStatus === 'all'    ? 'selected' : '' ?>>All</option>
                    <option value="active" <?= $filterStatus === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="frozen" <?= $filterStatus === 'frozen' ? 'selected' : '' ?>>Frozen</option>
                    <option value="banned" <?= $filterStatus === 'banned' ? 'selected' : '' ?>>Banned</option>
                </select>
            </div>

            <div class="user-filters__group">
                <label class="user-filters__label" for="filter-joined">Joined</label>
                <select id="filter-joined" name="joined" class="user-filters__select" onchange="this.form.submit()">
                    <option value=""      <?= $filterJoined === ''      ? 'selected' : '' ?>>Any time</option>
                    <option value="week"  <?= $filterJoined === 'week'  ? 'selected' : '' ?>>This week</option>
                    <option value="month" <?= $filterJoined === 'month' ? 'selected' : '' ?>>This month</option>
                    <option value="year"  <?= $filterJoined === 'year'  ? 'selected' : '' ?>>This year</option>
                </select>
            </div>
        </form>

        <p class="user-no-results" id="no-results-msg" <?= !empty($users) ? 'hidden' : '' ?>>No users matching your filters.</p>

        <div class="user-table-wrap" <?= empty($users) ? 'hidden' : '' ?>>
            <table class="user-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Plan</th>
                        <th>Expires</th>
                        <th>Status</th>
                        <th style="text-align:right">Joined</th>
                    </tr>
                </thead>
                <tbody id="user-tbody">
                <?php foreach ($users as $u):
                    $pfpPath = __DIR__ . '/../../database/profile_pictures/' . $u['user_id'] . '.png';
                    $pfpUrl  = file_exists($pfpPath)
                        ? '/database/profile_pictures/' . $u['user_id'] . '.png?v=' . filemtime($pfpPath)
                        : null;
                    $initials = '';
                    foreach (array_slice(array_filter(explode(' ', $u['name'])), 0, 2) as $w) {
                        $initials .= mb_strtoupper(mb_substr($w, 0, 1));
                    }
                    $isSelf    = $u['user_id'] === $session->getId();
                    $isBanned  = !(bool)$u['is_active'];
                    $isFrozen  = !$isBanned && ($u['sub_status'] === 'frozen');
                ?>
                    <tr class="user-row <?= $isBanned ? 'user-row--banned' : '' ?>">
                        <td class="user-row__actions">
                            <button type="button"
                                    class="btn-ghost btn-sm user-edit-btn"
                                    data-user-id="<?= (int)$u['user_id'] ?>"
                                    data-name="<?= htmlspecialchars($u['name'], ENT_QUOTES) ?>"
                                    data-username="<?= htmlspecialchars($u['username'], ENT_QUOTES) ?>"
                                    data-email="<?= htmlspecialchars($u['email'], ENT_QUOTES) ?>"
                                    data-phone="<?= htmlspecialchars($u['phone'] ?? '', ENT_QUOTES) ?>"
                                    data-role="<?= htmlspecialchars($u['role']) ?>"
                                    data-active="<?= $u['is_active'] ? '1' : '0' ?>"
                                    data-bio="<?= htmlspecialchars($u['bio'] ?? '', ENT_QUOTES) ?>"
                                    data-specializations="<?= htmlspecialchars($u['specializations'] ?? '', ENT_QUOTES) ?>"
                                    data-certifications="<?= htmlspecialchars($u['certifications'] ?? '', ENT_QUOTES) ?>"
                                    data-sub-end-date="<?= htmlspecialchars($u['sub_end_date'] ?? '', ENT_QUOTES) ?>"
                                    data-trainer-featured="<?= (int)($u['trainer_featured'] ?? 0) ?>"
                                    data-is-self="<?= $isSelf ? '1' : '0' ?>">
                                Edit
                            </button>
                        </td>
                        <td class="user-row__identity">
                            <?php if ($pfpUrl): ?>
                                <img src="<?= htmlspecialchars($pfpUrl) ?>" alt="" class="user-row__avatar">
                            <?php else: ?>
                                <div class="user-row__avatar user-row__avatar--initials"><?= htmlspecialchars($initials) ?></div>
                            <?php endif; ?>
                            <div>
                                <span class="user-row__name"><?= htmlspecialchars($u['name']) ?></span>
                                <span class="user-row__handle">@<?= htmlspecialchars($u['username']) ?></span>
                            </div>
                        </td>
                        <td class="user-row__email"><?= htmlspecialchars($u['email']) ?></td>
                        <td>
                            <span class="role-badge role-badge--<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span>
                        </td>
                        <td class="user-row__plan">
                            <?= $u['plan_name'] ? htmlspecialchars($u['plan_name']) : '<span class="user-row__no-plan">—</span>' ?>
                        </td>
                        <td class="user-row__expiry">
                            <?php if ($u['sub_end_date']):
                                $isExpired = $u['sub_end_date'] < date('Y-m-d');
                            ?>
                                <span <?= $isExpired ? 'class="user-row__expiry--expired"' : '' ?>>
                                    <?= date('j M Y', strtotime($u['sub_end_date'])) ?>
                                </span>
                            <?php else: ?>
                                <span class="user-row__no-plan">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($isBanned): ?>
                                <span class="status status--banned">Banned</span>
                            <?php elseif ($isFrozen): ?>
                                <span class="status status--frozen">Frozen</span>
                            <?php else: ?>
                                <span class="status status--active">Active</span>
                            <?php endif; ?>
                        </td>
                        <td class="user-row__date"><?= date('j M Y', strtotime($u['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </main>

    <?php include '../components/footer.php'; ?>

    <!-- Edit User Modal -->
    <div class="modal-backdrop" id="user-modal-backdrop"></div>
    <dialog id="user-modal" class="auth-modal user-modal">
        <button type="button" class="btn-ghost auth-modal__close" id="user-modal-close">&times;</button>
        <h2 class="auth-modal__title" id="user-modal-title">Edit User</h2>

        <div class="user-modal-tabs" id="user-modal-tabs">
            <button type="button" class="user-modal-tab active" data-tab="details">Details</button>
            <button type="button" class="user-modal-tab" data-tab="role">Role</button>
            <button type="button" class="user-modal-tab" data-tab="danger">Danger</button>
        </div>

        <!-- Details Tab -->
        <div class="user-modal-panel" id="tab-details">
            <form method="POST" action="../actions/action_admin_update_user.php">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <input type="hidden" name="action" value="update_details">
                <input type="hidden" name="user_id" id="edit-user-id">
                <input type="hidden" name="ref" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">

                <label for="edit-name">Name</label>
                <input type="text" id="edit-name" name="name" required>

                <label for="edit-email">Email</label>
                <input type="email" id="edit-email" name="email" required>

                <label for="edit-phone">Phone</label>
                <input type="text" id="edit-phone" name="phone">

                <div id="trainer-fields" hidden>
                    <label for="edit-bio">Bio</label>
                    <textarea id="edit-bio" name="bio" class="user-modal__textarea" rows="3"></textarea>

                    <label for="edit-spec">Specializations <span class="field-hint">(comma-separated)</span></label>
                    <input type="text" id="edit-spec" name="specializations">

                    <label for="edit-cert">Certifications <span class="field-hint">(comma-separated)</span></label>
                    <input type="text" id="edit-cert" name="certifications">
                </div>

                <button type="submit" class="btn-primary user-modal__submit">Save Changes</button>
            </form>

            <div id="trainer-feature-section" hidden>
                <hr class="user-modal__section-divider">
                <form method="POST" action="../actions/action_toggle_featured.php" data-feature-toggle>
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                    <input type="hidden" name="type" value="trainer">
                    <input type="hidden" name="id" id="feature-trainer-id">
                    <input type="hidden" name="return" value="/src/pages/admin-users.php">
                    <button type="submit" class="btn-ghost user-modal__submit" id="feature-trainer-btn"></button>
                </form>
            </div>

            <div id="subscription-section" hidden>
                <div class="user-modal__section-divider"></div>
                <form method="POST" action="../actions/action_admin_update_user.php" class="auth-modal__form">
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                    <input type="hidden" name="action" value="update_subscription">
                    <input type="hidden" name="user_id" id="sub-user-id">
                    <input type="hidden" name="ref" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">

                    <label for="edit-sub-end-date">Subscription End Date</label>
                    <input type="date" id="edit-sub-end-date" name="end_date">

                    <button type="submit" class="btn-primary user-modal__submit">Update Subscription</button>
                </form>
            </div>
        </div>

        <!-- Role Tab -->
        <div class="user-modal-panel" id="tab-role" hidden>
            <form method="POST" action="../actions/action_admin_update_user.php">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <input type="hidden" name="action" value="set_role">
                <input type="hidden" name="user_id" id="role-user-id">
                <input type="hidden" name="ref" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">

                <p class="user-modal__hint">Changing a member to trainer will create a trainer profile. Changing a trainer back to member keeps their profile data.</p>

                <label for="edit-role">Role</label>
                <select id="edit-role" name="role" class="user-filters__select user-modal__select">
                    <option value="member">Member</option>
                    <option value="trainer">Trainer</option>
                    <option value="admin">Admin</option>
                </select>

                <p class="user-modal__self-warn" id="role-self-warn" hidden>
                    You cannot change your own role.
                </p>

                <button type="submit" class="btn-primary user-modal__submit" id="role-submit">Change Role</button>
            </form>
        </div>

        <!-- Danger Tab -->
        <div class="user-modal-panel" id="tab-danger" hidden>
            <p class="user-modal__self-warn" id="danger-self-warn" hidden>
                You cannot ban or delete your own account.
            </p>

            <div id="danger-actions">
                <form method="POST" action="../actions/action_admin_update_user.php" class="danger-form">
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                    <input type="hidden" name="action" id="ban-action" value="ban">
                    <input type="hidden" name="user_id" id="ban-user-id">
                    <input type="hidden" name="ref" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
                    <div class="danger-row">
                        <div>
                            <p class="danger-row__title" id="ban-label">Ban User</p>
                            <p class="danger-row__desc" id="ban-desc">Prevent this user from logging in.</p>
                        </div>
                        <button type="submit" class="btn-primary btn-sm danger-btn" id="ban-btn">Ban</button>
                    </div>
                </form>

                <form method="POST" action="../actions/action_admin_update_user.php" class="danger-form" id="delete-form">
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="user_id" id="delete-user-id">
                    <div class="danger-row danger-row--delete">
                        <div>
                            <p class="danger-row__title">Permanently Delete</p>
                            <p class="danger-row__desc">Remove all data. This cannot be undone.</p>
                        </div>
                        <button type="button" class="btn-primary btn-sm danger-btn danger-btn--delete" id="delete-btn">Delete</button>
                    </div>
                </form>
            </div>

            <!-- Delete confirmation -->
            <div id="delete-confirm" hidden>
                <p class="user-modal__confirm-text">Are you sure you want to permanently delete <strong id="delete-confirm-name"></strong>? This cannot be undone.</p>
                <div class="user-modal__confirm-actions">
                    <button type="button" class="btn-primary btn-sm" id="delete-cancel">Cancel</button>
                    <button type="button" class="btn-primary btn-sm danger-btn--delete" id="delete-confirm-btn">Yes, Delete</button>
                </div>
            </div>
        </div>
    </dialog>

    <script>
    const backdrop = document.getElementById('user-modal-backdrop');
    const modal    = document.getElementById('user-modal');

    function switchTab(name) {
        document.querySelectorAll('.user-modal-tab').forEach(t => {
            t.classList.toggle('active', t.dataset.tab === name);
        });
        document.querySelectorAll('.user-modal-panel').forEach(p => {
            p.hidden = p.id !== 'tab-' + name;
        });
    }

    function openModal(u) {
        document.getElementById('user-modal-title').textContent = u.name;
        document.getElementById('edit-user-id').value  = u.id;
        document.getElementById('edit-name').value     = u.name;
        document.getElementById('edit-email').value    = u.email;
        document.getElementById('edit-phone').value    = u.phone;

        const trainerFields = document.getElementById('trainer-fields');
        const trainerFeatureSection = document.getElementById('trainer-feature-section');
        if (u.role === 'trainer') {
            trainerFields.removeAttribute('hidden');
            document.getElementById('edit-bio').value  = u.bio;
            document.getElementById('edit-spec').value = u.spec;
            document.getElementById('edit-cert').value = u.cert;
            trainerFeatureSection.removeAttribute('hidden');
            document.getElementById('feature-trainer-id').value = u.id;
            document.getElementById('feature-trainer-btn').textContent = u.trainerFeatured
                ? '★ Remove from Homepage'
                : '☆ Feature on Homepage';
        } else {
            trainerFields.setAttribute('hidden', '');
            trainerFeatureSection.setAttribute('hidden', '');
        }

        const subSection = document.getElementById('subscription-section');
        if (u.subEndDate) {
            subSection.removeAttribute('hidden');
            document.getElementById('sub-user-id').value       = u.id;
            document.getElementById('edit-sub-end-date').value = u.subEndDate;
        } else {
            subSection.setAttribute('hidden', '');
        }

        document.getElementById('role-user-id').value     = u.id;
        document.getElementById('edit-role').value        = u.role;
        document.getElementById('role-self-warn').hidden  = !u.isSelf;
        document.getElementById('role-submit').disabled   = u.isSelf;

        document.getElementById('ban-user-id').value       = u.id;
        document.getElementById('delete-user-id').value    = u.id;
        document.getElementById('danger-self-warn').hidden = !u.isSelf;
        document.getElementById('danger-actions').hidden   = u.isSelf;

        if (!u.isSelf) {
            const banAction = document.getElementById('ban-action');
            const banBtn    = document.getElementById('ban-btn');
            const banLabel  = document.getElementById('ban-label');
            const banDesc   = document.getElementById('ban-desc');
            if (u.active === '0') {
                banAction.value = 'unban';
                banBtn.textContent = 'Unban';
                banLabel.textContent = 'Unban User';
                banDesc.textContent  = 'Restore access for this user.';
            } else {
                banAction.value = 'ban';
                banBtn.textContent = 'Ban';
                banLabel.textContent = 'Ban User';
                banDesc.textContent  = 'Prevent this user from logging in.';
            }
        }

        switchTab('details');
        backdrop.classList.add('modal-backdrop--visible');
        modal.showModal ? modal.showModal() : modal.removeAttribute('hidden');
    }

    function closeModal() {
        backdrop.classList.remove('modal-backdrop--visible');
        modal.close ? modal.close() : modal.setAttribute('hidden', '');
        document.getElementById('delete-confirm').hidden = true;
        document.getElementById('danger-actions').hidden = false;
    }

    function attachEditListeners() {
        document.querySelectorAll('.user-edit-btn').forEach(btn => {
            btn.addEventListener('click', () => openModal({
                id:     btn.dataset.userId,
                name:   btn.dataset.name,
                email:  btn.dataset.email,
                phone:  btn.dataset.phone,
                role:   btn.dataset.role,
                active: btn.dataset.active,
                bio:    btn.dataset.bio,
                spec:            btn.dataset.specializations,
                cert:            btn.dataset.certifications,
                subEndDate:      btn.dataset.subEndDate,
                trainerFeatured: btn.dataset.trainerFeatured === '1',
                isSelf:          btn.dataset.isSelf === '1',
            }));
        });
    }

    function liveSearch(q) {
        const form     = document.getElementById('user-filters-form');
        const tbody    = document.getElementById('user-tbody');
        const tableWrap = tbody.closest('.user-table-wrap');
        const noMsg    = document.getElementById('no-results-msg');
        const params   = new URLSearchParams(new FormData(form));
        params.set('q', q);
        fetch('?' + params.toString())
            .then(r => r.text())
            .then(html => {
                const tmp = document.createElement('div');
                tmp.innerHTML = html;
                const newTbody = tmp.querySelector('#user-tbody');
                if (newTbody && newTbody.children.length > 0) {
                    tbody.innerHTML = newTbody.innerHTML;
                    tableWrap.removeAttribute('hidden');
                    noMsg.setAttribute('hidden', '');
                    attachEditListeners();
                } else {
                    tbody.innerHTML = '';
                    tableWrap.setAttribute('hidden', '');
                    noMsg.removeAttribute('hidden');
                }
            });
    }

    attachEditListeners();

    document.querySelectorAll('.user-modal-tab').forEach(tab => {
        tab.addEventListener('click', () => switchTab(tab.dataset.tab));
    });

    document.getElementById('user-modal-close').addEventListener('click', closeModal);
    backdrop.addEventListener('click', closeModal);

    document.getElementById('delete-btn').addEventListener('click', () => {
        const name = document.getElementById('user-modal-title').textContent;
        document.getElementById('delete-confirm-name').textContent = name;
        document.getElementById('danger-actions').hidden = true;
        document.getElementById('delete-confirm').hidden = false;
    });
    document.getElementById('delete-cancel').addEventListener('click', () => {
        document.getElementById('danger-actions').hidden = false;
        document.getElementById('delete-confirm').hidden = true;
    });
    document.getElementById('delete-confirm-btn').addEventListener('click', () => {
        document.getElementById('delete-form').submit();
    });
    </script>

    <script>
    document.getElementById('users-export-btn').addEventListener('click', () => {
        const now = new Date();
        const ts  = now.toLocaleString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        const iframe = document.createElement('iframe');
        iframe.style.cssText = 'position:fixed;width:0;height:0;border:0;visibility:hidden';
        iframe.src = '/src/pages/admin-users-export.php?ts=' + encodeURIComponent(ts);
        document.body.appendChild(iframe);
        iframe.onload = () => {
            iframe.contentWindow.print();
            iframe.contentWindow.addEventListener('afterprint', () => iframe.remove());
        };
    });
    </script>
    <script type="module">
        import { initFeatureSwap } from '../scripts/feature-swap.js';
        initFeatureSwap();
    </script>
</body>
</html>
