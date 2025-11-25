$(document).ready(function () {
    document.querySelectorAll('.double-input').forEach(input => {
        debugger;
        input.addEventListener('input', function () {

            this.value = this.value.replace(/[^0-9.]/g, '');

            const parts = this.value.split('.');
            if (parts.length > 2) {
                this.value = parts[0] + '.' + parts[1];
            }

            if (parts[1]?.length > 2) {
                parts[1] = parts[1].substring(0, 2);
                this.value = parts[0] + '.' + parts[1];
            }
        });
    });
});