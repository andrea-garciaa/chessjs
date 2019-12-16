// fonctions utilisant AJAX
//-------------------------


// obtenir les données client
function getSessionData(callback) {
    $.ajax({
        url: '/getsession.php', // retourne les données du client ou false s'il n'est pas connecté
    }).done(function(data) {
        callback(data);
    })
}

// se connecter
function login(input, callback) {
    $.ajax({
        url: '/login.php',
        method: 'POST',
        data: input
    }).done(function(data) {
        callback(data);
    });
}

// se déconnecter
function logout(callback) {
    $.ajax({
        url: '/logout.php',
    }).done(function(data) {
        callback(data);
    });
}

// créer un compte
function register(input, callback) {
    $.ajax({
        url: '/register.php',
        method: 'POST',
        data: input
    }).done(function(data) {
        callback(data);
    });
}


// Fonctions relatives à l'interface utilisateur
//-------------------------------------------------

function showMenu(nomUtilisateur) {
    $('body').append(
        $('<header />').append(
            $('<div class="bg-dark collapse" id="navbarHeader"/>').append(
                $('<div class="container"/>').append(
                    $('<div class="row"/>').append(
                        $('<div class="col-sm-8 col-md-7 py-4"/>').append(
                            $('<h4 class="text-white">Chess Tournament</h4>'),
                            $('<p class="text-muted" id="menudesc">Bienvenue dans le menu principal de Chess Tournament.</p>')
                        ),
                        $('<div class="col-sm-4 offset-md-1 py-4"/>').append(
                            $('<h4 class="text-white" id="menutitre">Accueil</h4>'),
                            $('<ul class="list-unstyled" id="menu"/>').append(
                                $('<li />').append(
                                    $('<a href="#" class="text-white" id="play">Jouer<a/>').click(showPlayPage)
                                ),
                                $('<li />').append(
                                    $('<a href="#" class="text-white" id="ranking">Classement</a>').click(showRankingPage)
                                ),
                                $('<li />').append(
                                    $(`<a href="#" class="text-white" id="account">Mon compte (<strong>${nomUtilisateur}</strong>)<a/>`).click(nomUtilisateur, showAccountPage)
                                ),
                                $('<li />').append(
                                    $('<a href="#" class="text-white" id="logout">Se déconnecter<a/>').click(function() {
                                        logout(function() {
                                            window.location.reload(true); // recharge la page après la déconnexion
                                        })
                                    })
                                )
                            )
                        )
                    )
                )
            ),
            $('<div class="navbar navbar-dark bg-dark shadow-sm" id="menuBar"/>').append(
                $('<div class="container d-flex justify-content-between"/>').append(
                    $('<a href="/" class="navbar-brand d-flex align-items-center" id="navbarTitle"><strong>Chess Tournament</strong></a>'),
                    $('<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarHeader" aria-controls="navbarHeader" aria-expanded="true" aria-label="Toggle navigation"><span class="navbar-toggler-icon"/></button>').click(function() {
                        $('#navbarTitle strong').fadeToggle();
                    })
                )
            )
        )
    )
}

function showLoginPage() {
    $('#signupform').remove(); // enlève le formulaire de création de compte de la page (si présent)
    $('body').attr('class', 'text-center').append(
        $('<form class="my-form" id="loginform"/>').append(
            $('<h1 class="h3 mb-3 font-weight-normal">Connectez-vous pour continuer</h1>'),
            $('<input class="form-control" type="text" name="nick_or_mail" placeholder="Pseudo ou adresse e-mail"/>'),
            $('<input class="form-control mb-3" type="password" name="pass" placeholder="Mot de passe"/>'),
            $('<div />').append(
                $('<button class="btn btn-lg btn-primary btn-block" type="submit">Connexion</button>')
            ),
            $('<p class="mt-2 mb-2 text-muted"/>').append(
                'Ou bien ', $('<a href="#">créez un compte</a>').click(showRegisterPage), ' si ce n\'est pas encore fait.'
            )
        ).submit(function() {
            login($(this).serialize(), function(data) {
                if (data.success)
                    window.location.reload(true); // recharge la page pour actualiser le fait que l'utilisateur soit maintenant connecté
                else {
                    $('#loginerror').remove();
                    $('#loginform').append(
                        $('<p class="mt-2 mb-2" id="loginerror"/>').html(data.message).css('color', 'red')
                    )
                    //https://api.jqueryui.com/shake-effect/
                    $('#loginerror').effect("shake");
                }
            });
            return false;
        })
    );
}


function showRegisterPage() {
    $('#loginform').remove(); // enlève le formulaire de connexion si présent
    $('body').attr('class', 'text-center').append(
        $('<form class="my-form" id="signupform"/>').append(
            $('<h1 class="h3 mb-3 font-weight-normal">Créez un compte</h1>'),
            $('<input class="form-control" type="text" name="nick" placeholder="Pseudo"/>'),
            $('<input class="form-control" type="email" name="email" placeholder="Adresse e-mail"/>'),
            $('<input class="form-control mb-3" type="password" name="pass" placeholder="Mot de passe"/>'),
            $('<button class="btn btn-lg btn-primary btn-block" type="submit">Créer</button>'),
            $('<p />').attr('class', 'mt-2 mb-2 text-muted').append(
                'Ou ', $('<a href="#">connectez-vous</a>').click(showLoginPage), ' si vous possédez déjà un compte.'
            )
        ).submit(function() {
            register($(this).serialize(), function(data) {
                if (data.success)
                    window.location.reload(true); // recharge la page pour actualiser le fait que l'utilisateur soit maintenant connecté
                else {
                    $('#signuperror').remove();
                    $('#signupform').append(
                        $('<p class="mt-2 mb-2" id="signuperror"/>').html(data.message).css('color', 'red')
                    )
                    //https://api.jqueryui.com/shake-effect/
                    $('#signuperror').effect("shake");
                }
            });
            return false;
        })
    );
}


function showPlayPage() {
    $('#content').empty(); // vide le conteneur principal de la page
    $('.navbar-toggler').click();
    $('#menutitre').html('Jouer');

    $('#content').attr('class', 'text-center');

    getSessionData(function(data) {
        if (data.gameid) {
            // une partie est déjà en cours

            $('#menudesc').html('Bienvenue sur la page de jeu de Chess Tournament. Reprise de la partie.');

            $.ajax({
                url: '/getgame.php',    // retourne les données de la partie en cours (ma couleur de pions, le tableau des pièces, l'id de l'adversaire)
                data: 'gameid=' + data.gameid + '&play=1' // getgame.php peut être également utilisé pour regarder une partie, alors on précise qu'on est joueur (sinon php retournera simplement les id des deux joueurs)
            }).done(function(gamedata) {
                if (gamedata.success) {
                    if (gamedata.wonid) {
                        // wonid est l'id du gagnant, s'il ne vaut pas NULL c'est que la partie est finie
                        if (gamedata.wonid == data.id) {
                            $('#content').append(
                                $('<h3>Vous avez gagné</h3>')
                            )
                        } else {
                            $('#content').apppend(
                                $('<h3>Vous avez perdu</h3>')
                            )
                        }
                    }
                    else
                        showChess(gamedata);
                } else {
                    $.ajax({
                        url: '/leavegame.php'
                    }).done(showPlayPage); // efface les données de partie dans le serveur (car apparemment, la partie n'est plus valide, ainsi on pourra en rechercher une autre)
                }
            })
        } else {
            // aucune partie n'est en cours
            // invite le joueur à chercher une partie

            $('#content').append(
                $('<form id="form-search" class="form-inline"/>').css({'margin-top': '25px', 'margin-left': 'auto', 'margin-right': 'auto', 'max-width': '50%'}).append(
                    $('<div class="form-group mx-sm-3 mb-2"/>').append(
                        $('<input class="form-control" type="text" name="nick" id="nomadversaire" placeholder="Nom du joueur (facultatif)"/>')
                    ),
                    $('<button class="btn btn-primary mb-2" type="submit">Rechercher une partie</button>')
                ).submit(function() {
                    $('#content').append(
                        $('<img id="loadingimg" src="img/loading.gif" alt="Loading" />')
                    );

                    $.ajax({
                        url: '/matchmaking.php',
                        data: $(this).serialize(),
                        method: 'POST' // pour laisser la barre d'URL telle qu'elle est
                    }).done(function(match) {
                        if (match.success) {
                            showChess(match);
                        } else if (match.waiting) {
                            let poll = function() {
                                $.ajax({
                                    url: '/matchmaking.php',
                                    data: 'waiting=true' // le serveur nous a déjà placé en file d'attente, on lui indique qu'on attend un joueur
                                }).done(function(match) {
                                    if (match.success) {
                                        clearInterval(pollInterval);
                                        showChess(match);
                                    } else if (match.waiting) {
                                        $('#errormsg').remove();
                                        $('#content').append(
                                            $('<h2 id="errormsg">Aucune partie n\'a été trouvée pour l\'instant.</h2>')
                                        );
                                    } else if (match.message) {
                                        $('#errormsg').remove();
                                        $('#content').append(
                                            $('<p id="errormsg" />').css('color', 'red').html(match.message)
                                        );
                                        $('#errormsg').effect("shake");
                                    }
                                });
                            };
                            let pollInterval = setInterval(poll, 3000);
                        } else {
                            $('#errormsg').remove();
                            $('#content').append(
                                $('<p id="errormsg" />').css('color', 'red').html(match.message)
                            );
                            $('#errormsg').effect("shake");
                        }
                    })
                })
            )
        }
    });
}

function showChess(matchdata) {
    $('#errormsg').remove();
    $('#loadingimg').remove();
    $('#form-search').remove();

    $('#content').append(
        $('<button class="btn btn-lg btn-primary btn-block">Quitter la partie</button>').click(function() {
            $.ajax({
                url: '/leavegame.php'
            }).done(showPlayPage);
        })
    );

    let game = new ChessGame();
    game.init(matchdata.iswhite, matchdata.opponent, matchdata.pieces);
}


function showRankingPage() {
    $('#content').empty(); // vide la page actuelle pour y placer le classement
    $('.navbar-toggler').click();
    $('#menutitre').html('Classement');
    $('#menudesc').html('Bienvenue sur le classement de Chess Tournament');

    $('#content').attr('class', 'text-center').append(
        $('<h3>Classement général</h3>')
    )

    $('<table id="ranktable"/>').css({'margin-left': 'auto', 'margin-right': 'auto'}).append(
        $('<tr />').append(
            $('<th>Pseudo</th>'),
            $('<th>Victoires</th>'),
            $('<th>Défaites</th>')
        )
    ).appendTo($('#content'));

    $.ajax({
        url: '/ranking.php',
    }).done(function(data) {
       if (data.success) {
           for (let result of data.results) {
               $('#ranktable').append(
                   $('<tr />').append(
                       $('<td />').html(result.nick),
                       $('<td />').html(result.wins),
                       $('<td />').html(result.losses),
                   )
               )
           }
       } else {
         $('<p style="color: red">Impossible de récupérer le classement</p>');
       }
    });
}

function showAccountPage(nomUtilisateur) {
    $('#content').empty(); // vide la page actuelle pour y placer le classement
    $('.navbar-toggler').click();
    $('#menutitre').html('Mon compte');
    $('#menudesc').html('Bienvenue sur la page de votre compte Chess Tournament');

    $('#content').append(
        $('<h3>Cette section n\'est pas encore disponible</h3>')
    );
}