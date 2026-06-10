document.addEventListener('DOMContentLoaded', () => {
    const timerElement = document.getElementById('timer');
    if (!timerElement) return;

    let timeLeft = parseInt(timerElement.dataset.seconds || '0', 10);
    const form = document.getElementById('testForm');

    const tick = () => {
        const min = Math.floor(timeLeft / 60);
        const sec = timeLeft % 60;
        timerElement.textContent = `${String(min).padStart(2, '0')}:${String(sec).padStart(2, '0')}`;

        if (timeLeft <= 0 && form) {
            form.submit();
            return;
        }

        timeLeft--;
        setTimeout(tick, 1000);
    };

    tick();
});
