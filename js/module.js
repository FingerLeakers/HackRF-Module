registerController('HackRFController', ['$api', '$scope', '$interval', function($api, $scope, $interval) {
    $scope.foundBoard       = false;
    $scope.availableHackRFs = "";
    $scope.running          = false;
    $scope.installed        = false;
    $scope.installling      = false;


    $scope.getHackRF_Info = (function() {
        $api.request({
            module: 'HackRF',
            action: 'getHackRF_Info'
        }, function(response) {
            $scope.foundBoard = response.foundBoard;

            if (response.foundBoard === true) {
                $scope.availableHackRFs = response.availableHackRFs;
            }
        });
    });

    $scope.hackrf_Checker = (function() {
        $api.request({
            module: 'HackRF',
            action: 'hackrf_Checker'
        }, function(response) {
            if(response.installed === true) {
                $scope.installed = true;
                $scope.installing = false;
                $scope.getHackRF_Info();
                $interval.cancel($scope.install_interval);
            } else {
                $scope.installed = false;
            }
        });
    });

    $scope.hackrf_Install = (function() {
        $api.request({
            module: 'HackRF',
            action: 'hackrf_Install'
        }, function(response) {
            if(response.installing === true) {
                $scope.installing = true;
                $scope.install_interval = $interval(function(){
                    $scope.hackrf_Checker();
                }, 1000);
            }
        });
    });

    $scope.hackrf_Uninstall = (function() {
        $api.request({
            module: 'HackRF',
            action: 'hackrf_Uninstall'
        }, function(response) {
            if(response.success === true) {
                $scope.hackrf_Checker();
                $scope.getHackRF_Info();
            }
        });
    });

    $scope.hackrf_Checker();
    $scope.getHackRF_Info();

    $scope.$on('$destroy', function() {
        $interval.cancel($scope.install_interval);
    });
}]);

registerController('HackRFSettingsController', ['$api', '$scope', '$timeout', function($api, $scope, $timeout) {
    $scope.mode            = "rx";
    $scope.sampleRate      = "";
    $scope.centerFreq      = "";
    $scope.filename        = "";
    $scope.amp             = false;
    $scope.antpower        = false;
    $scope.sampleRateError = false;


    $scope.hackRF_Transfer = (function() {
        $api.request({
            module: 'HackRF',
            action: 'doHackRF_Transfer',
            mode: $scope.mode,
            sampleRate: $scope.sampleRate,
            centerFreq: $scope.centerFreq,
            filename: $scope.filename,
            amp: $scope.amp,
            antpower: $scope.antpower
        }, function(response) {
            if(response.success === true) {
                $scope.running = true;
            } else if(response.success === false) {
                $scope.sampleRateError = true;
                $timeout(function() {
                    $scope.sampleRateError = false;
                }, 3000);
            }
        });
    });

    
    $scope.hackRF_Stop = (function() {
        $api.request({
            module: 'HackRF',
            action: 'doHackRF_Stop'
        }, function(response) {
            if (response.success === true) {
                $scope.running = false;
            }
        });
    });
}]);

registerController('HackRFLoggingController', ['$api', '$scope', function($api, $scope) {
    $scope.log = "";

    $scope.getHackRFLog = (function() {
        $api.request({
            module: 'HackRF',
            action: 'getHackRF_Log'
        }, function(response) {
            if (response.success === true) {
                $scope.log = response.log;
            }
        });
    });

    $scope.getHackRFLog();

}]);