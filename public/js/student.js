document.addEventListener('DOMContentLoaded', () => {
	const taskTable = document.getElementById('tasks-table');
	const taskSearch = document.querySelector('[data-task-search]');
	const taskStatusFilter = document.querySelector('[data-task-status-filter]');
	const taskPriorityFilter = document.querySelector('[data-task-priority-filter]');

	function filterTaskRows() {
		if (!taskTable) {
			return;
		}

		const query = (taskSearch?.value || '').trim().toLowerCase();
		const status = taskStatusFilter?.value || '';
		const priority = taskPriorityFilter?.value || '';
		const rows = taskTable.querySelectorAll('[data-task-row]');

		rows.forEach((row) => {
			const rowStatus = row.getAttribute('data-status') || '';
			const rowPriority = row.getAttribute('data-priority') || '';
			const rowSearch = row.getAttribute('data-search') || '';

			const matchesQuery = query === '' || rowSearch.includes(query);
			const matchesStatus = status === '' || rowStatus === status;
			const matchesPriority = priority === '' || rowPriority === priority;

			row.style.display = matchesQuery && matchesStatus && matchesPriority ? '' : 'none';
		});
	}

	taskSearch?.addEventListener('input', filterTaskRows);
	taskStatusFilter?.addEventListener('change', filterTaskRows);
	taskPriorityFilter?.addEventListener('change', filterTaskRows);
	filterTaskRows();

	function toggleModal(modalId, isOpen) {
		const modal = document.getElementById(modalId);
		if (!modal) {
			return null;
		}

		modal.classList.toggle('open', isOpen);
		return modal;
	}

	function closeAllStudentModals() {
		document.querySelectorAll('.modal-overlay.open').forEach((modal) => {
			modal.classList.remove('open');
		});
		const deleteName = document.getElementById('delete-name');
		if (deleteName) {
			deleteName.textContent = '';
		}
	}

	function openModal(mode = 'add', subjectName = '', coefficient = '') {
		const subjectModal = document.getElementById('subject-modal');
		const examModal = document.getElementById('exam-modal');

		if (subjectModal) {
			const title = document.getElementById('modal-title');
			const nameInput = document.getElementById('m-name');
			const coeffInput = document.getElementById('m-coeff');

			if (title) {
				title.textContent = mode === 'edit' ? `Edit subject${subjectName ? `: ${subjectName}` : ''}` : 'Add subject';
			}

			if (nameInput && subjectName) {
				nameInput.value = subjectName;
			}

			if (coeffInput && coefficient !== '') {
				coeffInput.value = coefficient;
			}

			subjectModal.classList.add('open');
			return;
		}

		if (examModal) {
			const title = examModal.querySelector('.modal-title');
			if (title) {
				title.textContent = subjectName ? `Schedule exam for ${subjectName}` : 'Schedule exam';
			}

			examModal.classList.add('open');
		}
	}

	function closeModal() {
		closeAllStudentModals();
	}

	function handleOverlayClick(event) {
		if (event.target && event.target.classList && event.target.classList.contains('modal-overlay')) {
			closeAllStudentModals();
		}
	}

	function saveSubject() {
		const subjectModal = document.getElementById('subject-modal');
		if (subjectModal) {
			subjectModal.classList.remove('open');
		}
		window.alert('Subject actions are still wired as a front-end demo.');
	}

	function confirmDelete(subjectName) {
		const deleteModal = document.getElementById('delete-modal');
		const deleteName = document.getElementById('delete-name');

		if (deleteName) {
			deleteName.textContent = subjectName || 'this subject';
		}

		if (deleteModal) {
			deleteModal.classList.add('open');
		} else if (window.confirm(`Delete ${subjectName || 'this item'}?`)) {
			window.alert('Delete action is not connected yet.');
		}
	}

	function closeDeleteModal() {
		const deleteModal = document.getElementById('delete-modal');
		if (deleteModal) {
			deleteModal.classList.remove('open');
		}
		const deleteName = document.getElementById('delete-name');
		if (deleteName) {
			deleteName.textContent = '';
		}
	}

	function filterSubjects(query) {
		const lowerQuery = (query || '').trim().toLowerCase();

		document.querySelectorAll('.subject-card[data-name]').forEach((card) => {
			const name = card.getAttribute('data-name') || '';
			card.style.display = name.includes(lowerQuery) ? '' : 'none';
		});
	}

	function filterExams(query) {
		const lowerQuery = (query || '').trim().toLowerCase();

		document.querySelectorAll('.exam-card[data-subject]').forEach((card) => {
			const subject = card.getAttribute('data-subject') || '';
			card.style.display = subject.includes(lowerQuery) ? '' : 'none';
		});
	}

	function submitSettingsForm(action, values) {
		const form = document.createElement('form');
		form.method = 'POST';
		form.action = window.location.pathname;

		const actionInput = document.createElement('input');
		actionInput.type = 'hidden';
		actionInput.name = 'action';
		actionInput.value = action;
		form.appendChild(actionInput);

		Object.entries(values).forEach(([name, value]) => {
			const input = document.createElement('input');
			input.type = 'hidden';
			input.name = name;
			input.value = value;
			form.appendChild(input);
		});

		document.body.appendChild(form);
		form.submit();
	}

	function changePassword() {
		const currentPassword = document.getElementById('current-pw')?.value || '';
		const newPassword = document.getElementById('new-pw')?.value || '';
		const confirmPassword = document.getElementById('confirm-pw')?.value || '';

		if (!currentPassword || !newPassword || !confirmPassword) {
			window.alert('Please fill in the current password, new password, and confirmation.');
			return;
		}

		if (newPassword.length < 8) {
			window.alert('New password must be at least 8 characters.');
			return;
		}

		if (newPassword !== confirmPassword) {
			window.alert('Passwords do not match.');
			return;
		}

		submitSettingsForm('password', {
			current_password: currentPassword,
			new_password: newPassword,
			confirm_password: confirmPassword,
		});
	}

	function saveAllSettings() {
		const firstName = document.getElementById('firstname')?.value || '';
		const lastName = document.getElementById('lastname')?.value || '';
		const email = document.getElementById('email')?.value || '';

		if (!firstName || !lastName || !email) {
			window.alert('Please complete the profile fields before saving.');
			return;
		}

		submitSettingsForm('profile', {
			prenom: firstName,
			nom: lastName,
			email,
		});
	}

	window.openModal = openModal;
	window.closeModal = closeModal;
	window.handleOverlayClick = handleOverlayClick;
	window.saveSubject = saveSubject;
	window.confirmDelete = confirmDelete;
	window.closeDeleteModal = closeDeleteModal;
	window.filterSubjects = filterSubjects;
	window.filterExams = filterExams;
	window.changePassword = changePassword;
	window.saveAllSettings = saveAllSettings;
});
