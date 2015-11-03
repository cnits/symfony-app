/*
 * Code Owner: Cnit*
 * Modified Date: 10/9/2015
 * Modified By: Phong Lam
 */
(function(){
    'use strict';
    var app;
    if(angular.isDefined(angular.module("app.fireBase"))){
        app = angular.module("app.fireBase");
    } else {
        app = angular.module("app.fireBase", ["firebase"]);
    }

    app.value('fbURL', 'https://cnit.firebaseio.com/data')
    .service('fbRef', function(fbURL) {
        return new Firebase(fbURL);
    })
    .service('fbAuth',
        function($q, $firebase, $firebaseAuth, fbRef) {
            var auth;
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
    .service('fbProject',
        function($q, $firebase, fbRef, fbAuth) {
            var self = this;
            self.fetch = function () {
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

            self.persist = function() {
                console.log("Invoke Persist Func");
            };

            self.remove = function() {
                console.log("Invoke Remove Func");
            }
        }
    );
}).call(angular);