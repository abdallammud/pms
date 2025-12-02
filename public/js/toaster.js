class Toaster {
	constructor() {
		// Create a container for the toasts if it doesn't exist
		if (!document.querySelector('.my-toaster')) {
			const container = document.createElement('div');
			container.className = 'my-toaster';
			container.style.position = 'fixed';
			container.style.width = '400px';
			document.body.appendChild(container);
		}
		this.container = document.querySelector('.my-toaster');
	}

	showToast(type, title, message, options = {}) {
		return new Promise((resolve) => {
			const alertDiv = document.createElement('div');
			alertDiv.className = `alert alert-${type} bg-${type} border-0 alert-dismissible fade show`;

			// Set the width dynamically or use default
			alertDiv.style.width = options.width || '100%';
			alertDiv.style.marginBottom = '10px';
			const autoHide = options.hide === undefined ? true : options.hide; // Default to true if not provided
			const autoHideTime = options.duration || 5000;

			alertDiv.innerHTML = `
			<div class="d-flex align-items-center">
			<div class="font-35 text-white"><span class="bi bi-${this.getIcon(type)}"></span></div>
			<div class="ms-3">
			<h6 class="mb-0 text-white bolder">${title}</h6>
			<div class="text-white">${message}</div>
			</div>
			</div>
			<button type="button" class="btn-close cursor" data-bs-dismiss="alert" aria-label="Close"></button>
			`;

			// Append the alert to the container
			this.container.appendChild(alertDiv);

			// Set dynamic position or use default
			this.container.style.top = options.top || '10px';
			this.container.style.right = options.right || '10px';
			this.container.style.zIndex = '999999';
			if (options.center) {
				this.container.style.right = '0px';
				this.container.style.left = '50%';
				this.container.style.transform = 'translate(-50%, 0)';
			}

			// Auto-dismiss based on the autoHide option
			if (autoHide) {
				setTimeout(() => {
					alertDiv.classList.remove('show');
					alertDiv.classList.add('fade');
					setTimeout(() => {
						this.container.removeChild(alertDiv);
						resolve(true);
					}, 150);
				}, autoHideTime);
			}

			// Dismiss when the close button is clicked
			alertDiv.querySelector('.btn-close').addEventListener('click', () => {
				this.container.removeChild(alertDiv);
				resolve(true);
			});
		});
	}

	getIcon(type) {
		switch (type) {
			case 'danger':
				return 'x-circle';
			case 'success':
				return 'check-circle';
			case 'warning':
				return 'exclamation-circle';
			case 'primary':
				return 'info-circle';
			default:
				return 'info-circle';
		}
	}

	error(message, title = '', options = {}) {
		const msgTitle = title || 'Danger Alert';
		return this.showToast('danger', msgTitle, message, options);
	}

	success(message, title = '', options = {}) {
		const msgTitle = title || 'Success Alert';
		return this.showToast('success', msgTitle, message, options);
	}

	warning(message, title = '', options = {}) {
		const msgTitle = title || 'Warning Alert';
		return this.showToast('warning', msgTitle, message, options);
	}

	primary(message, title = '', options = {}) {
		const msgTitle = title || 'Primary Alert';
		return this.showToast('primary', msgTitle, message, options);
	}
}


const toaster = new Toaster();