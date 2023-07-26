document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('time.date').forEach(time => {
        time.textContent = new Date(time.dateTime).toLocaleString();
    })
});
