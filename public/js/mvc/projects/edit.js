'use strict';

;(function ($, Vue, G) {
    let application = new Vue({
        el: '#app',
        data: {
            project: G.project || {name: '', rate: '', is_active: 1},
            childProjects: G.project ? G.project.children : [],
            submitUrl: G.submitUrl,
            error: '',
            disableSubmitButton: false
        },
        methods: {
            addChildProject: function () {
                this.childProjects.push({id:'', rate: '', is_active: 1, name: ''});
            },
            removeChildProject: function (indexOfItem) {
                this.childProjects.splice(indexOfItem, 1)
            },
            submit: function () {
                this.error = '';
                this.disableSubmitButton = true;
                let app = this;
                $.ajax({
                    url: this.submitUrl,
                    method: 'POST',
                    data: $('#edit-form').serialize(),
                    success: function success(r) {
                        window.location = '/projects';
                    },
                    error: function error(r) {
                        app.disableSubmitButton = false;
                        app.error = 'Форма содержит ошибки. Заполните все поля. Поле Rate принимает только целые цифры.'
                    }
                });
            },
            getChildInputName: function (name, index) {
                return 'child[' + index + '][' + name + ']'
            },
        }
    });
})(jQuery, Vue, window._globals || {});
