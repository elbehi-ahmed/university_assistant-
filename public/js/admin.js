document.addEventListener('DOMContentLoaded', () => {
	document.querySelectorAll('[data-confirm-message]').forEach((form) => {
		form.addEventListener('submit', (event) => {
			const message = form.dataset.confirmMessage || 'Are you sure you want to continue?';

			if (!window.confirm(message)) {
				event.preventDefault();
			}
		});
	});
});
