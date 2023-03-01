document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('time').forEach(time => {
        time.textContent = new Date(time.dateTime).toLocaleString();
    })
});
