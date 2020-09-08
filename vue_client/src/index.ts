import Vue from "vue";
import GameListComponent from "./components/GameList.vue"
import StatsBoxComponent from "./components/StatsBox.vue"

let v = new Vue({
    el: "#app",
    template: `
        <div class="game-wrapper">
        <game-list-component/>
        <div id="gameField">
            <table id="target_ocean" class="ocean"></table>
            <table id="ocean" class="ocean"></table>
            <stats-box-component/>
            <div id="winner"></div>
        </div>
        </div>`,
    components: {
        GameListComponent,
        StatsBoxComponent
    }
});
