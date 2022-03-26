!function () {
    "use strict";

    const eternium = {
        async systemStatus() {
            return this.fetch('v2/getSystemStatus');
        },

        async fetch(method) {
            return fetch(`https://eternium.alex-tsarkov.workers.dev/api/${method}`)
                .then(response => {
                    if (response.ok) {
                        return response.json();
                    }
                    throw new Error(`Method ${method} got HTTP ${response.status}`);
                });
        },
    };

    const status = document.getElementById('status');
    if (status) {
        status.classList.remove('d-none')
        eternium.systemStatus()
            .then(({ status }) => {
                status.classList.replace('alert-secondary', 'alert-' + (status.code ? 'danger' : 'success'));
                status.textContent = `Game version: ${status.version}. ${status.message}`;
            })
            .catch(() => { });
    }
}();
