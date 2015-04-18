(function () {
    'use strict';
    
    angular.module('App', [
        'ngRoute',
        'ngSanitize',
        'App.Store'
    ]);
    
    /**
     * @ngInject
     */
    function ConfigFn($routeProvider, $locationProvider) {
        $routeProvider.otherwise('/store');
                
        $locationProvider.html5Mode(true);
    }
    
    /**
     * @ngInject
     */
    function AppCtrl($scope, AppData) {
        $scope.appTitle = AppData.mainTitle;
    }
    
    angular
        .module('App')
        .config(ConfigFn)
        .controller('AppController', AppCtrl)
        .constant('AppData', {
            mainTitle: 'Record Store'
        });
    
}());