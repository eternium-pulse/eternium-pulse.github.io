const eternium = new class Eternium {
    async gameEvents() {
        return this._fetch('events');
    }

    async leaderboard(id) {
        return this._fetch(`leaderboards/${id}`);
    }

    async leaderboards() {
        return this._fetch('leaderboards');
    }

    async ranking(id, ranking) {
        return this._fetch(`leaderboards/${id}/rankings/${ranking}`);
    }

    async rankings(id, { page = 1, pageSize = 25 }) {
        return this._fetch(`leaderboards/${id}/rankings?${new URLSearchParams({ page, pageSize })}`);
    }

    async systemStatus() {
        return this._fetch('status');
    }

    async _fetch(url) {
        return fetch(new URL(url, 'https://eternium.pages.dev/api/v1/').href).then(async response => response.ok ? response.json() : Promise.reject(response.status));
    }
};
