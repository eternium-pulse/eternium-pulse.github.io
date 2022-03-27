const eternium = {
    async gameEvents() {
        return this._fetchV3('getGameEvents').then(({ events }) => events);
    },

    async leaderboard(id) {
        return this._fetchMfp(`leaderboards/${id}`);
    },

    async leaderboards() {
        return this._fetchMfp('leaderboards');
    },

    async news() {
        return this._fetchV2('getNews').then(({ news }) => news);
    },

    async ranking(id, ranking) {
        return this._fetchMfp(`leaderboards/${id}/rankings/${ranking}`);
    },

    async rankings(id, { page = 1, pageSize = 25 }) {
        return this._fetchMfp(`leaderboards/${id}/rankings?${new URLSearchParams({ page, pageSize })}`);
    },

    async systemStatus() {
        return this._fetchV2('getSystemStatus').then(({ status }) => status);
    },

    async _fetchMfp(url) {
        return this._fetch(url, 'https://eternium-mfp.alex-tsarkov.workers.dev/api/');
    },

    async _fetchV2(url) {
        return this._fetch(url, 'https://eternium.alex-tsarkov.workers.dev/api/v2/');
    },

    async _fetchV3(url) {
        return this._fetch(url, 'https://eternium.alex-tsarkov.workers.dev/api/v3/');
    },

    async _fetch(url, base) {
        return fetch(new URL(url, base).href).then(async response => response.ok ? response.json() : Promise.reject(response.status));
    },
};
