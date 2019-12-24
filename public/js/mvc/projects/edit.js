'use strict';

;(function ($, Vue, G) {
    var application = new Vue({
        el: '#app',
        data: {
            project: G.project || { name: '', rate: '', is_active: 1 },
            childProjects: G.project ? G.project.children : [],
            submitUrl: G.submitUrl,
            error: '',
            disableSubmitButton: false
        },
        methods: {
            addChildProject: function addChildProject() {
                this.childProjects.push({ id: '', rate: '', is_active: 1, name: '' });
            },
            removeChildProject: function removeChildProject(indexOfItem) {
                this.childProjects.splice(indexOfItem, 1);
            },
            submit: function submit() {
                this.error = '';
                this.disableSubmitButton = true;
                var app = this;
                $.ajax({
                    url: this.submitUrl,
                    method: 'POST',
                    data: $('#edit-form').serialize(),
                    success: function success(r) {
                        window.location = '/projects';
                    },
                    error: function error(r) {
                        app.disableSubmitButton = false;
                        app.error = 'The form contains errors. Fill in all the fields. The Rate field accepts only numbers (dot separator, no more than 2 characters after the separator).';
                    }
                });
            },
            getChildInputName: function getChildInputName(name, index) {
                return 'child[' + index + '][' + name + ']';
            }
        }
    });
})(jQuery, Vue, window._globals || {});