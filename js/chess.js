// classe qui permet de gérer le jeu d'échecs


function ChessGame()
{
    this.table = []; // la table du jeu
    this.pieces = []; // la liste des pièces du jeu

    /**
     *
     * @param iswhite : [string] ma couleur de pions est blanche (permet d'afficher la bonne couleur des pions et de savoir si mes coordonnées partent d'en haut ou d'en bas)
     * @param opponent : [object] les informations sur l'adversaire
     * @param pieces : [array] la liste des pièces et leurs informations
     */
    this.init = function(iswhite, opponent, pieces) {
        if (!opponent || !pieces)
            return false;

        let self = this;

        this.iswhite = iswhite;
        this.opponent = opponent;
        this.pieces = pieces;

        let table = $('<table cellspacing="0"></table>').css({'border': '1px solid black', 'table-layout': 'fixed', 'margin-left': 'auto', 'margin-right': 'auto', 'margin-top': '25px'}).appendTo($('#content'));

        let whitecase = false;

        // crée les cases du tableau (vides pour l'instant)
        for (let i=0; i < 8; ++i) {
            this.table[i] = [];
            whitecase = !whitecase;
            let tr = $('<tr />').appendTo(table);

            for (let j=0; j < 8; ++j) {
                this.table[i][j] = {_white: (!whitecase ? (j % 2 ? true : false) : (j % 2 ? false : true))};
                $('<td />').append($('<span style="font-size:200%"/>')).appendTo(tr).css({
                    'height': '50px',
                    'width': '50px',
                    'background-color': (!whitecase ? (j % 2 ? 'rgba(223, 217, 194, 1)' : 'rgba(117, 67, 20, 1)') : (j % 2 ? 'rgba(117, 67, 20, 1)' : 'rgba(223, 217, 194, 1)'))
                }).mouseenter(function () {
                    $(this).css('background-color', 'grey');
                }).mouseleave({x: j, y: i}, function (coords) {
                    if (self.table[coords.data.y][coords.data.x]._white)
                        $(this).css('background-color', 'rgba(223, 217, 194, 1)');
                    else
                        $(this).css('background-color', 'rgba(117, 67, 20, 1)');
                }).click({x: j, y: i}, function(coords) {
                    if (self.selectedCase) {    // une case est déjà sélectionnée
                        $.ajax({
                            url: '/game.php',
                            data: 'move=1&fx=' + (!self.iswhite ? self.selectedCase.x : 7 - self.selectedCase.x) + '&fy=' + (!self.iswhite ? self.selectedCase.y : 7 - self.selectedCase.y) + '&tx=' + (!self.iswhite ? coords.data.x : 7 - coords.data.x) + '&ty=' + (!self.iswhite ? coords.data.y : 7 - coords.data.y)
                        }).done(function(response) {
                            if (!response.success) {
                                $('#content').empty();
                                $('#content').append(
                                    $('<h3>La partie n\'est plus disponible</h3>')
                                )
                                $.ajax({
                                    url: '/leavegame.php'
                                });
                            } else if (response.illegalmove) {
                                $('#errormsg').remove();
                                $('#content').append(
                                    $('<p id="errormsg" style="color: red;"/>').html(response.illegalmove)
                                );
                            } else {
                                if (response.won) {
                                    $('#content').empty();
                                    $('#content').append(
                                        $('<h3 style="margin-top: 25px">Vous avez gagné</h3>')
                                    );
                                    $('#content').append(
                                        $('<button class="btn btn-primary">Fermer</button>').click(function() {
                                            $.ajax({
                                                url: '/leavegame.php' // la partie est terminée, on le dit au serveur car il ne supprime pas lui même la partie, étant donnée que l'autre adversaire peut ne pas savoir à ce moment que la partie est terminée (avant la prochaine update)
                                            }).done(showPlayPage);
                                        })
                                    )
                                } else if (response.lose) {
                                    $('#content').empty();
                                    $('#content').append(
                                        $('<h3 style="margin-top: 25px">Vous avez perdu</h3>')
                                    );
                                    $('#content').append(
                                        $('<button class="btn btn-primary">Fermer</button>').click(function() {
                                            $.ajax({
                                                url: '/leavegame.php' // la partie est terminée, on le dit au serveur car il ne supprime pas lui même la partie, étant donnée que l'autre adversaire peut ne pas savoir à ce moment que la partie est terminée (avant la prochaine update)
                                            }).done(showPlayPage);
                                        })
                                    )
                                } else {
                                    if (response.pieces) {
                                        self.pieces = response.pieces;
                                        self.show();
                                    }
                                }

                            }
                        });
                        delete self.selectedCase;
                    }
                    else
                        self.selectedCase = coords.data;

                });
            }
        }

        this.show();

        // ajoute les infos sur le joueur

        $.ajax({
            url: '/getplayer.php',
            data: 'id=' + this.opponent
        }).done(function(data) {
            if (data.success) {
                $('#content').append(
                    '<hr>',
                    $('<div style="margin-left: auto; margin-right: auto; margin-top: 50px;"/>').append(
                        $('<h4 />').html('Adversaire: <strong>' + data.result.nick + '</strong>'),
                        $('<p />').html('id: <strong>' + data.result.id + '</strong>'),
                        $('<p />').html('victoires: <strong>' + data.result.wins +  '</strong>'),
                        $('<p />').html('défaites: <strong>' + data.result.losses + '</strong>')
                    )
                )
            } else {
                $('#content').append(
                    '<hr>',
                    $('<div style="margin-left: auto; margin-right: auto; margin-top: 50px;"/>').append(
                        $('<h4 color="red">Impossible de trouver les informations sur l\'adversaire</h4>')
                    )
                )
            }

        });

        let poll = function() {
            $.ajax({
                url: '/game.php',
                data: 'update=1',
            }).done(function(response) {
                if (!response.success) {
                    clearInterval(pollInterval);
                    $('#content').empty();
                    $('#content').append(
                        $('<h3 style="margin-top: 25px">La partie n\'est plus disponible</h3>')
                    )
                } else {
                    if (response.won) {
                        $('#content').empty();
                        $('#content').append(
                            $('<h3 style="margin-top: 25px">Vous avez gagné</h3>')
                        );
                        $('#content').append(
                            $('<button class="btn btn-primary">Fermer</button>').click(function() {
                                $.ajax({
                                    url: '/leavegame.php' // la partie est terminée, on le dit au serveur car il ne supprime pas lui même la partie, étant donnée que l'autre adversaire peut ne pas savoir à ce moment que la partie est terminée (avant la prochaine update)
                                }).done(showPlayPage);
                            })
                        )
                    } else if (response.lose) {
                        $('#content').empty();
                        $('#content').append(
                            $('<h3 style="margin-top: 25px">Vous avez perdu</h3>')
                        );
                        $('#content').append(
                            $('<button class="btn btn-primary">Fermer</button>').click(function() {
                                $.ajax({
                                    url: '/leavegame.php' // la partie est terminée, on le dit au serveur car il ne supprime pas lui même la partie, étant donnée que l'autre adversaire peut ne pas savoir à ce moment que la partie est terminée (avant la prochaine update)
                                }).done(showPlayPage);
                            })
                        )

                    } else if (response.pieces) {
                        if (response.tour == (self.iswhite ? 'white' : 'black'))
                            $('#errormsg').remove();

                        self.pieces = response.pieces;
                        if (self.selectedCase) {
                            // si la pièce qu'on avait sélectionnée a été supprimé entre 2 updates, on déselectionne la case
                            let removed = true;
                            for (let i in self.pieces) {
                                let piece = self.pieces[i];
                                if (((!self.iswhite ? piece.x : 7 - piece.x) == self.selectedCase.x) && ((!self.iswhite ? piece.y : 7 - piece.y) == self.selectedCase.y)) {
                                    removed = false;
                                    break;
                                }
                            }
                            if (removed)
                                delete self.selectedCase;
                        }
                        self.show();
                    }
                }
            });
        };

        let pollInterval = setInterval(poll, 1000); // 1000ms d'intervalle devrait être suffisant
    };

    // affiche les pièces dans les cases du tableau précédemment construites

    this.show = function() {

        for (let i=0; i < 8; ++i) {
            for (let j=0; j < 8; ++j) {
                let tr = $($('tr')[j]);
                let td = $($('td', tr)[i]);
                $('span', td).html('');
            }
        }

        let selectedCaseChecked = false;

        for (let i in this.pieces) {
            let piece = this.pieces[i];

            let x = (!this.iswhite ? piece.x : (7 - piece.x)); // échange les places pour que nos pièces apparaissent toujours en bas (c'est toujours le joueur blanc qui est en bas au niveau des coordonnées)
            let y = (!this.iswhite ? piece.y : (7 - piece.y));

            let tr = $($('tr')[y]);
            let td = $($('td', tr)[x]);


            if (this.table[y][x])
                Object.assign(this.table[y][x], piece);
            else
                this.table[y][x] = piece;

            //td.css({'background-position': 'center', 'background-repeat': 'no-repeat', 'background-size': 'cover'});

            if (!selectedCaseChecked && this.selectedCase && this.selectedCase.x == x && this.selectedCase.y == y) {
                td.css('background-color', 'green');
                selectedCaseChecked = true;
            }

            switch (piece.type) {
                case 'pion':
                    //td.css('background-image', 'url("/img/fou.jpg")');
                    $('span', td).html(piece.white ? '\u2659' : '\u265f');
                    break;
                case 'tour':
                    $('span', td).html(piece.white ? '\u2656' : '\u265c');
                    break;
                case 'cavalier':
                    $('span', td).html(piece.white ? '\u2658' : '\u265e');
                    break;
                case 'fou':
                    $('span', td).html(piece.white ? '\u2657' : '\u265d');
                    break;
                case 'roi':
                    $('span', td).html(piece.white ? '\u2655' : '\u265b');
                    break;
                case 'reine':
                    $('span', td).html(piece.white ? '\u2654' : '\u265a');
                    break;
                default:
                    break;
            }
        }
    }
}

