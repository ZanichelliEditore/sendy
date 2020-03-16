<template>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" @click="onClick(rootElem.link)" :href=rootElem.link>{{rootElem.label}}</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto ml-lg-4">
                <li class="nav-item active" v-for="(elem, index) in navElemList" v-bind:key="index" >
                    <a class="nav-link" :href=elem.link @click="onClick(elem.link)">{{elem.label}}</a>
                </li>
            </ul>
        </div>
        <!-- <ul class="navbar-nav ml-auto ml-md-0">
            <li class="nav-item active">
                <a class="nav-link" :href=logoutRoute @click="onClick(logoutRoute)">Logout</a>
            </li>
        </ul> -->
    </nav>
</template>

<script>
    import { EventBus } from '../../event-bus.js';

    export default {
        data() {
            return {
                rootElem: {label: "Sendy", link:"/jobs"},
                navElemList: [{label: "Jobs", link:"/jobs"}, {label: "Failed Jobs", link:"/failedJobs"}],
                logoutRoute: "/logout"
            };
        },
        methods: {
            onClick(redirectUrl) {
                const splittedUrl = window.location.href.split('/');
                const editableElemId = parseInt(splittedUrl[splittedUrl.length-1]);

                if (_.isNaN(editableElemId)) {
                    return;
                }
            }
        }
};
</script>
