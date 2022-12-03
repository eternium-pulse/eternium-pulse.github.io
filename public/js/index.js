Promise.all([
    new Promise((resolve, reject) => {
        const elem = document.getElementById('status');
        if (elem) {
            elem.classList.replace('d-none', 'd-block');
            resolve(elem.querySelector('.placeholder'));
        } else {
            reject();
        }
    }),
    eternium.systemStatus(),
]).then(([elem, { version, code, message }]) => {
    elem.classList.replace('placeholder', 'alert-' + (code ? 'danger' : 'success'));
    elem.textContent = `Game version: ${version}. ${message}`;
});
