Promise.all([
    new Promise((resolve, reject) => {
        const elem = document.getElementById('status');
        if (elem) {
            elem.classList.remove('d-none');
            resolve(elem);
        } else {
            reject();
        }
    }),
    eternium.systemStatus(),
]).then(([elem, { version, code, message }]) => {
    elem.classList.replace('alert-secondary', 'alert-' + (code ? 'danger' : 'success'));
    elem.textContent = `Game version: ${version}. ${message}`;
});
