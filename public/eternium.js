const eternium = {
    async systemStatus() {
        return this._fetchV2('getSystemStatus').then(({ status }) => status);
    },

    async _fetchV2(method) {
        return fetch(`https://eternium.alex-tsarkov.workers.dev/api/v2/${method}`).then(this._handle);
    },

    async _handle(response) {
        return response.ok ? response.json() : Promise.reject(response.status);
    },
};
