<script lang="ts">
import {Component, Vue} from "vue-property-decorator";
import http from "../http-common";
import {EventBus} from '../event-bus';

@Component
export default class GameList extends Vue {
    private games: Game[] = [];
    private current_user = ''

    mounted() {
        http
            .get('/login')
            .then((response) => {
                this.current_user = response.data.current_user;
                this.loadGames();
            });
    }

    private loadGames() {
        http
            .get(this.current_user)
            .then(response => {
                for (const gameKey in response.data.games) {
                    http
                        .get(response.data.games[gameKey])
                        .then(response => {
                            this.games.push(response.data);
                        });

                }
            });
    }

    private onNewGame() {
        http
            .post('/api/games', {})
            .then(response => {
                this.games.push(response.data);
                EventBus.$emit('update-game', response.data);
            });
    }

    public loadGame(game: object) {
        EventBus.$emit('update-game', game);
    }
}
</script>

<template>
    <div>
        Wählen Sie ein Spiel:
        <ul id="games">
            <li v-bind:key="game.id" v-for="game in games" v-on:click="loadGame(game)">{{ game.created }}</li>
        </ul>
        <button v-show="current_user" v-on:click="onNewGame">Neues Spiel</button>
    </div>
</template>
