Vue.component('blah', {
    //
})

const app = new Vue({
    el: '#root',
    data: {
        // admin_props: {{!}}
        choices: {},
    },

    methods: {
        onSubmit() {
            console.log('submit btn clicked')
        },

        update() {

        }
    }
});