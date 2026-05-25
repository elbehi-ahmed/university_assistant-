document.addEventListener('DOMContentLoaded', () => {
	const modalOverlay = document.getElementById('modal-overlay');
	const classForm = document.getElementById('teacher-class-form');
	const taskForm = document.getElementById('teacher-task-form');

	function setModalTitle(text) {
		const title = document.getElementById('modal-title-text');
		if (title) {
			title.textContent = text;
		}
	}

	function openModal() {
		if (!modalOverlay) {
			return;
		}

		if (taskForm) {
			taskForm.reset();
			setModalTitle('New Assignment');
		} else if (classForm) {
			classForm.reset();
			setModalTitle('Create New Class');
		}

		modalOverlay.classList.add('open');
	}

	function closeModal() {
		modalOverlay?.classList.remove('open');
	}

	function saveClass() {
		classForm?.requestSubmit();
	}

	function saveTask() {
		taskForm?.requestSubmit();
	}

	async function deleteTask(deleteUrl, csrfToken, title, button) {
		if (!window.confirm(`Delete "${title}"? This cannot be undone.`)) {
			return;
		}

		button?.setAttribute('disabled', 'disabled');

		try {
			const response = await fetch(deleteUrl, {
				method: 'POST',
				headers: {
					'X-Requested-With': 'XMLHttpRequest',
					'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
					'Accept': 'application/json',
				},
				body: new URLSearchParams({ _token: csrfToken }),
			});

			const payload = await response.json().catch(() => null);

			if (!response.ok || !payload?.success) {
				throw new Error(payload?.message || 'Task deletion failed.');
			}

			window.location.reload();
		} catch (error) {
			window.alert(error instanceof Error ? error.message : 'Task deletion failed.');
			button?.removeAttribute('disabled');
		}
	}

	function openEditModal(name) {
		openModal();
		if (taskForm) {
			setModalTitle(`Edit Assignment — ${name}`);
		} else {
			setModalTitle(`Edit Class — ${name}`);
		}
	}

	function deleteClass(button) {
		const deleteUrl = button?.dataset.deleteUrl;
		const csrfToken = button?.dataset.csrfToken;
		const className = button?.dataset.className || 'this class';
		const card = button?.closest('.class-card');

		if (!deleteUrl || !csrfToken || !card) {
			return;
		}

		if (!window.confirm(`Delete "${className}"? This cannot be undone.`)) {
			return;
		}

		button?.setAttribute('disabled', 'disabled');

		fetch(deleteUrl, {
			method: 'POST',
			headers: {
				'X-Requested-With': 'XMLHttpRequest',
				'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
				'Accept': 'application/json',
			},
			body: new URLSearchParams({ _token: csrfToken }),
		})
			.then(async (response) => {
				const payload = await response.json().catch(() => null);

				if (!response.ok || !payload?.success) {
					throw new Error(payload?.message || 'Class deletion failed.');
				}

				card.remove();
			})
			.catch((error) => {
				window.alert(error instanceof Error ? error.message : 'Class deletion failed.');
				button?.removeAttribute('disabled');
			});
	}

	function selectColor(dot) {
		const container = dot.closest('.color-picker-row');
		container?.querySelectorAll('.color-dot').forEach((item) => item.classList.remove('selected'));
		dot.classList.add('selected');
	}

	function filterClasses(query) {
		document.querySelectorAll('.class-card[data-name]').forEach((card) => {
			const name = card.dataset.name.toLowerCase();
			card.style.display = name.includes(query.toLowerCase()) ? '' : 'none';
		});
	}

	modalOverlay?.addEventListener('click', (event) => {
		if (event.target === modalOverlay) {
			closeModal();
		}
	});

	window.openModal = openModal;
	window.closeModal = closeModal;
	window.saveClass = saveClass;
	window.saveTask = saveTask;
	window.deleteTask = deleteTask;
	window.openEditModal = openEditModal;
	window.deleteClass = deleteClass;
	window.selectColor = selectColor;
	window.filterClasses = filterClasses;
});
