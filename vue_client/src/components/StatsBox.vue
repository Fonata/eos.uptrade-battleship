<script lang="ts">
import {Component, Vue} from "vue-property-decorator";
import {EventBus} from '../event-bus';

@Component
export default class StatsBox extends Vue {
    private hits = 0
    private misses = 0

    created() {
        EventBus.$on('update-game', (game: Game) => {
            this.hits = StatsBox.countChars('H', game) + StatsBox.countChars('S', game);
            this.misses = StatsBox.countChars('M', game);
        })
    }

    private static countChars(char: string, game: Game) {
        let found = 0;
        for (let i = 0; i < 110; i++) {
            if (game.target_ocean[i] === char) found++;
        }
        return found;
    }
}
</script>

<template>
    <table id="stats">
        <tr>
            <th>Treffer:</th>
            <td id="hits">
                <span title="Treffer">{{ hits }}</span>/<span title="Schüsse">{{ hits + misses }}</span>
            </td>
        </tr>
        <tr>
            <th>letzter Gegnerschuss:</th>
            <td id="last_shot"></td>
        </tr>
    </table>
</template>
