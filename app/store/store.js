(function () {
    'use strict';
    
    angular.module('App.Store', ['ngRoute', 'ngResource']);
    
    function ConfigFn($routeProvider) {
        $routeProvider
            .when('/store', {
                controller: 'StoreController',
                controllerAs: 'store',
                templateUrl: 'app/store/store.tpl.html',
                resolve: {
                    items: function (StoreService) {
                        return new StoreService.load();
                    }
                }
            });
    }
    
    /**
     * @ngInject
     */
    function StoreCtrl(StoreService, items) {
        var self = this;
        self.items = items;
        
        self.album = new StoreService.resource();
        
        self.addItem = function () {
            StoreService.create(self.album).then(function (response) {
                if (response.error === 0) {
                    self.items.push(self.album);
                } else {
                    alert("Errore!!!");
                }
            }, function () {
                alert("Errore!!!");
            });
        };
        
    }
    
    /**
     * @ngInject
     */
    function StoreServiceFn($resource, $q, $http) {
        var service = {};
        
        service.resource = $resource('api/store/:id',
                                     {id: '@id'},
                                     {
                remove: {method: 'DELETE'}
            });
        
        service.load = function () {
            var defer = $q.defer();
            service.resource.query(function (data) {
                defer.resolve(data);
            }, function () {
                defer.reject('Errore caricamento dati');
            });
            return defer.promise;
        };
        
        service.create = function (formData) {
            return $http
                .post('api/store/index.php', formData)
                .then(function (res) {
                    return res.data;
                });
        };
        
        return service;
    }
    
    function AlbumList(StoreService) {
        function linkFn(scope, element, attrs) {
            scope.remove = function (index) {
                if (confirm("Sei sicuro?")) {
                    StoreService.resource.remove({id: scope.items[index].id}, function (data) {
                        if (data.error !== 1) {
                            scope.items.splice(index, 1);
                        }
                    });
                }
            };
        }
        
        return {
            restrict: 'E',
            replace: true,
            scope: {
                items: '='
            },
            templateUrl: 'app/store/albumList.tpl.html',
            link: linkFn
        };
        
    }
     
    angular
        .module('App.Store')
        .config(ConfigFn)
        .controller('StoreController', StoreCtrl)
        .factory('StoreService', StoreServiceFn)
        .directive('albumList', AlbumList);
}());