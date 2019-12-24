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
    formatMinutes: function formatMinutes(minutes, short) {
        var hours = parseInt(minutes / 60).toString();
        minutes = (minutes % 60).toString();

        if (short) {
            return "00".substring(0, 2 - hours.length) + hours + ':' + "00".substring(0, 2 - minutes.length) + minutes;
        }

        var hoursFormat = ['hour', 'hour', 'hours'];
        var minutesFormat = ['minute', 'minutes', 'minutes'];

        return hours + ' ' + this.nounEnding(hours, hoursFormat) + ' ' + minutes + ' ' + this.nounEnding(minutes, minutesFormat);
    }
};
