document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('time:not([datetime^=P])').forEach(time => {
        time.textContent = new Date(time.dateTime).toLocaleString();
    })
});
