const eternium = new class Eternium {
    async gameEvents() {
        return this._fetchMM('getGameEvents').then(({ events }) => events);
    }

    async leaderboard(id) {
        return this._fetchMFP(`leaderboards/${id}`);
    }

    async leaderboards() {
        return this._fetchMFP('leaderboards');
    }

    async ranking(id, ranking) {
        return this._fetchMFP(`leaderboards/${id}/rankings/${ranking}`);
    }

    async rankings(id, { page = 1, pageSize = 25 }) {
        return this._fetchMFP(`leaderboards/${id}/rankings?${new URLSearchParams({ page, pageSize })}`);
    }

    async systemStatus() {
        return this._fetchMM('getSystemStatus').then(({ status }) => status);
    }

    async systemStatusList() {
        return this._fetchMM('getSystemStatusList').then(({ status }) => status);
    }

    async _fetch(url, base) {
        return fetch(new URL(url, base).href).then(async response => response.ok ? response.json() : Promise.reject(response.status));
    }

    async _fetchMFP(url) {
        return this._fetch(url, 'https://eternium-mfp.alex-tsarkov.workers.dev/api/');
    }

    async _fetchMM(url) {
        return this._fetch(url, 'https://eternium.alex-tsarkov.workers.dev/api/v2/');
    }
};
