<header class="top-header">
	<nav class="navbar navbar-expand align-items-center gap-4">
		<div class="btn-toggle">
			<!-- <a href="javascript:;"><i class="material-icons-outlined">menu</i></a> -->
		</div>
		<div class="search-bar flex-grow-1">

		</div>
		<ul class="navbar-nav gap-1 nav-right-links align-items-center">
			<!-- <li class="nav-item d-lg-none mobile-search-btn">
				<a class="nav-link" href="javascript:;">
					<i class="material-icons-outlined">search</i>
				</a>
			</li> -->

			<li class="nav-item dropdown">
				<a class="nav-link dropdown-toggle dropdown-toggle-nocaret" href="javascript:;"
					data-bs-toggle="dropdown">
					<!-- <span class="material-symbols-outlined">light_mode</span> -->
					<i class="bi bi-columns-gap"></i>
				</a>
				<ul class="dropdown-menu dropdown-menu-end">
					<?php if (check_auth_permission('property_create')): ?>
						<li>
							<button class="dropdown-item d-flex align-items-center py-2" href="javascript:;"
								data-bs-toggle="modal" data-bs-target="#addPropertyModal">
								<i class="bi bi-building me-2"></i> Create Property
							</button>
						</li>
					<?php endif; ?>

					<?php if (check_auth_permission('unit_create')): ?>
						<li>
							<button class="dropdown-item d-flex align-items-center py-2" href="javascript:;"
								data-bs-toggle="modal" data-bs-target="#addUnitModal">
								<i class="bi bi-door-open me-2"></i> Create Property Unit
							</button>
						</li>
					<?php endif; ?>

					<?php if (check_auth_permission('tenant_create') || check_auth_permission('lease_create') || check_auth_permission('invoice_create') || check_auth_permission('payment_create')): ?>
						<li>
							<hr class="dropdown-divider">
						</li>

						<?php if (check_auth_permission('tenant_create')): ?>
							<li>
								<button class="dropdown-item d-flex align-items-center py-2" href="javascript:;"
									data-bs-toggle="modal" data-bs-target="#addTenantModal">
									<i class="bi bi-person-plus-fill me-2"></i> Register Tenant
								</button>
							</li>
						<?php endif; ?>

						<?php if (check_auth_permission('lease_create')): ?>
							<li>
								<a class="dropdown-item d-flex align-items-center py-2" href="<?= baseUri(); ?>/add_lease">
									<i class="bi bi-file-earmark-text me-2"></i> Create Lease
								</a>
							</li>
						<?php endif; ?>

						<?php if (check_auth_permission('invoice_create')): ?>
							<li>
								<a class="dropdown-item d-flex align-items-center py-2" href="javascript:;"
									onclick="loadModal('accounting', 'add_invoice', '#addInvoiceModal')">
									<i class="bi bi-receipt-cutoff me-2"></i> Issue invoice
								</a>
							</li>
						<?php endif; ?>

						<?php if (check_auth_permission('payment_create')): ?>
							<li>
								<a class="dropdown-item d-flex align-items-center py-2" href="javascript:;"
									onclick="loadModal('accounting', 'add_receipt', '#addReceiptModal')">
									<i class="bi bi-cash-stack me-2"></i> Record Receipt
								</a>
							</li>
						<?php endif; ?>
					<?php endif; ?>

					<?php if (check_auth_permission('maintenance_create') || check_auth_permission('maintenance_assign')): ?>
						<li>
							<hr class="dropdown-divider">
						</li>

						<?php if (check_auth_permission('maintenance_create')): ?>
							<li>
								<a class="dropdown-item d-flex align-items-center py-2" href="javascript:;"
									onclick="loadModal('maintenance', 'create_request', '#addRequestModal')">
									<i class="bi bi-tools me-2"></i> Register Maintenance Request
								</a>
							</li>
						<?php endif; ?>

						<?php if (check_auth_permission('maintenance_assign')): ?>
							<li>
								<a class="dropdown-item d-flex align-items-center py-2" href="javascript:;"
									onclick="loadModal('maintenance', 'assign_request', '#assignMaintenanceModal')">
									<i class="bi bi-person-check me-2"></i> Assign Maintenance Request
								</a>
							</li>
						<?php endif; ?>
					<?php endif; ?>


				</ul>
			</li>

			<li class="nav-item dropdown">
				<a href="javascrpt:;" class="dropdown-toggle dropdown-toggle-nocaret" data-bs-toggle="dropdown">
					<!-- <img src="<?= baseUri(); ?>/public/images/avatars/<?= $_SESSION['avatar']; ?>" class="rounded-circle p-1 border" width="45" height="45" alt=""> -->
					<img src="https://picsum.photos/seed/user123/40/40.jpg" alt="User" class="rounded-circle p-1 border"
						width="45" height="45" alt="">
				</a>
				<div class="dropdown-menu dropdown-user dropdown-menu-end shadow">
					<a class="dropdown-item  gap-2 py-2" href="javascript:;">
						<div class="d-flex align-items-center">
							<!-- <img src="<?= baseUri(); ?>/public/images/avatars/<?= $_SESSION['avatar']; ?>" class="rounded-circle p-1 shadow mb-3" width="90" height="90" alt=""> -->
							<img src="https://picsum.photos/seed/user123/40/40.jpg" alt="User"
								class="rounded-circle p-1 smr-10 border" width="45" height="45" alt="">
							<div>
								<h5 class="user-name mb-0 fw-bold"><?= $_SESSION['user_name']; ?></h5>
								<p class="mb-0 text-muted"><?= $_SESSION['user_role']; ?></p>
							</div>
						</div>
					</a>
					<hr class="dropdown-divider">
					<a class="dropdown-item d-flex align-items-center gap-2 py-2"
						href="<?= baseUri(); ?>/employees/show/<?= $_SESSION['user_id']; ?>">
						<i class="material-icons-outlined">person_outline</i>
						Profile
					</a>
					<a class="dropdown-item d-flex align-items-center gap-2 py-2" href="<?= baseUri(); ?>/settings/">
						<i class="material-icons-outlined">local_bar</i>
						Setting
					</a>
					<a class="dropdown-item d-flex align-items-center gap-2 py-2" href="<?= baseUri(); ?>/dashboard/">
						<i class="material-icons-outlined">dashboard</i>
						Dashboard
					</a>

					<hr class="dropdown-divider">
					<a class="dropdown-item d-flex align-items-center gap-2 py-2" href="<?= baseUri(); ?>/logout">
						<i class="material-icons-outlined">power_settings_new</i>
						Logout
					</a>
				</div>
			</li>

		</ul>
	</nav>
</header>