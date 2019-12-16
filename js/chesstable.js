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
        if (!iswhite || !opponent)
            return false;

        this.iswhite = iswhite;
        this.opponent = opponent;
        this.pieces = pieces;

        let table = $('<table cellspacing="0"></table>').css({'border': '1px solid black', 'table-layout': 'fixed'}).appendTo('#content');

        // crée les cases du tableau (vides pour l'instant)
        for (var i=0; i < 8; ++i) {
            let tr = $('<tr />').appendTo(table);
            this.table[i] = [];
            for (var j=0; j < 8; ++j)
                this.table[i][j] = $('<td />').appendTo(tr).css({'height': '30px', 'width': '30px', 'color': 'rgba(0, 0, 0, 0)'});
        }

        for (piece of pieces) {
            let x = !iswhite ? (7 - piece.x) : piece.x; // échange les places pour que nos pièces apparaissent toujours en bas (c'est toujours le joueur blanc qui est en bas au niveau des coordonnées)
            let y = !iswhite ? (7 - piece.y) : piece.y;

            switch (piece.type) {
                case 'pion':
                    break;
                case 'tour':
                    break;
                case 'cavalier':
                    break;
                case 'fou':
                    break;
                case 'roi':
                    break;
                case 'reine':
                    break;
                default:
                    break;
            }
        }
    }
}


