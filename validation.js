document.addEventListener('DOMContentLoaded', () => {
    const forms = document.querySelectorAll('form[data-validate="true"]');

    forms.forEach((form) => {
        form.addEventListener('submit', (e) => {
            const requiredFields = form.querySelectorAll('[required]');
            let valid = true;

            requiredFields.forEach((field) => {
                if (!field.value.trim()) {
                    field.style.borderColor = '#dc2626';
                    valid = false;
                } else {
                    field.style.borderColor = '';
                }
            });

            if (!valid) {
                e.preventDefault();
                alert('Пожалуйста, заполните обязательные поля.');
            }
        });
    });
});
