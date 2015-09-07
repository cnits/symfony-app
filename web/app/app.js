/*
 * Code Owner:
 * Modified Date: 05/27/2015
 * Modified By: Phong Lam
 */
'use strict';
(function(){
    var app = angular.module('app', ['ngRoute', 'firebase', "app.fireBase.Ctrl"])
        .value('fbURL', 'https://cnit.firebaseio.com/data')
        .service('fbRef', function(fbURL) {
            console.log(1);
            return new Firebase(fbURL);
        })
        .service('fbAuth', function($q, $firebase, $firebaseAuth, fbRef) {
            var auth;
            console.log(2);
            return function () {
                if (auth) {
                    return $q.when(auth);
                }
                var authObj = $firebaseAuth(fbRef);
                if (authObj.$getAuth()) {
                    return $q.when(auth = authObj.$getAuth());
                }
                var deferred = $q.defer();
                authObj.$authAnonymously().then(function(authData) {
                    auth = authData;
                    deferred.resolve(authData);
                });
                return deferred.promise;
            }
        })

        .service('Projects', function($q, $firebase, fbRef, fbAuth) {
            var self = this;
            console.log(3);
            this.fetch = function () {
                if (this.projects) {
                    return $q.when(this.projects);
                }
                return fbAuth().then(function(auth) {
                    var deferred = $q.defer();
                    var ref = fbRef.child('projects-fresh/' + auth.auth.uid);
                    var $projects = $firebase(ref);
                    ref.on('value', function(snapshot) {
                        if (snapshot.val() === null) {
                            $projects.$set(window.projectsArray);
                        }
                        self.projects = $projects.$asArray();
                        deferred.resolve(self.projects);
                    });

                    //Remove projects list when no longer needed.
                    ref.onDisconnect().remove();
                    return deferred.promise;
                });
            };
        })

        .config(function($routeProvider) {
            $routeProvider
                .when('/', {
                    controller:'ProjectListController as projectList',
                    templateUrl:'/app/components/fireBase/views/list.html',
                    resolve: {
                        projects: function (Projects) {
                            console.log(4);
                            return Projects.fetch();
                        }
                    }
                })
                .when('/edit/:projectId', {
                    controller:'EditProjectController as editProject',
                    templateUrl:'/components/fireBase/views/detail.html'
                })
                .when('/new', {
                    controller:'NewProjectController as editProject',
                    templateUrl:'/components/fireBase/views/detail.html'
                })
                .otherwise({
                    redirectTo:'/'
                });
        })

        .controller('ProjectListController', function(projects) {
            var projectList = this;console.log(5);
            projectList.projects = projects;
        })

        .controller('NewProjectController', function($location, Projects) {
            var editProject = this;console.log(6);
            editProject.save = function() {
                Projects.$add(editProject.project).then(function(data) {
                    $location.path('/');
                });
            };
        })

        .controller('EditProjectController',
            function($location, $routeParams, Projects) {
                var editProject = this;console.log(7);
                var projectId = $routeParams.projectId,
                    projectIndex;

                editProject.projects = Projects.projects;
                projectIndex = editProject.projects.$indexFor(projectId);
                editProject.project = editProject.projects[projectIndex];

                editProject.destroy = function() {
                    editProject.projects.$remove(editProject.project).then(function(data) {
                        $location.path('/');
                    });
                };

                editProject.save = function() {
                    editProject.projects.$save(editProject.project).then(function(data) {
                        $location.path('/');
                    });
                };
            }
        );
}).call();