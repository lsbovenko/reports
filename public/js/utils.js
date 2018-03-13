var Utils =  {
    nounEnding: function (number, endingArray) {
        var ending = '';

        number = number % 100;
        if (number >= 11 && number <= 19) {
            ending = endingArray[2];
        }
        else {
            var i = number % 10;
            switch (i) {
                case (1):
                    ending = endingArray[0];
                    break;
                case (2):
                case (3):
                case (4):
                    ending = endingArray[1];
                    break;
                default:
                    ending = endingArray[2];
            }
        }
        return ending;
    },
    formatMinutes: function formatMinutes(minutes) {
        var hours = parseInt(minutes / 60);

        minutes = minutes % 60;

        return hours + ' ' + this.nounEnding(hours, ['час', 'часа', 'часов']) + ' ' + minutes + ' ' + this.nounEnding(minutes, ['минута', 'минуты', 'минут']);
    }
};
