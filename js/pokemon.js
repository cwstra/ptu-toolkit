/**
 * Scripts for Player Client
 * @author Will Stephenson
 */
/**
 * JSON data of loaded Pokemon
 * @type JSON
 */
var character_data = {}, moves_data = [], battle_data = {}, afflictions = [], dex_data = {};

var currentMove = null;
var character_id = null; //TODO: replace with character_data["ID"]

/**
 * Peer to Peer Connection
 * @type DataConnection
 */
var connection = null;


/**
 * JQuery Bindings & Initialization
 */
$(function () {

    $("#btn-do-dmg").click(function () {
        sendMessage(host_id, JSON.stringify({
            "type": "battle_damage",
            "target": character_id,
            "moveType": $("#dmg-type").val(),
            "isSpecial": $("#do-dmg-sp").is(':checked'),
            "damage": $("#do-dmg").val()
        }))
    });

    $("#btn-do-heal").click(function () {
        var max_hp = character_data['level'] + character_data['hp'] * 3 + 10;

        character_data["health"] += $("#do-heal").val();

        if (character_data["health"] > max_hp)
            character_data["health"] = max_hp;

        sendMessage(host_id, JSON.stringify({
            "type": "pokemon_update",
            "pokemon": character_id,
            "field": "health",
            "value": character_data["health"]
        }));
    });

    $("#stages").find("input").change(function () {
        sendMessage(host_id, JSON.stringify({
            "type": "pokemon_setcs",
            "pokemon": character_id,
            "field": $(this).attr("data-target"),
            "value": $(this).val()
        }));

        if ($(this).attr("id") == "stage-acc")
            $("#move-acc-bonus").val($(this).val());

        if ($(this).attr("id") == "stage-speed") {
            var s = character_data["speed"] * getStageMultiplier(parseInt($(this).val()));
            $("#speed").html(s);
        }
    });

    // Link Move Accuracy Bonus with Accuracy 'Stage'
    $("#move-acc-bonus").change(function () {
        $("#stage-acc").val($(this).val());
    });

    $(".progress").click(function () {
        window.alert($(this).attr("data-hp"));
    });

    $("input[type='checkbox']").parent().click(function () {
        if ($(this).find("input").attr("checked") == "checked") {
            $(this).find("input").removeAttr("checked").val("off").trigger("click").trigger("change");
        }
        else {
            $(this).find("input").attr("checked", "checked").val("on").trigger("click").trigger("change");
        }
    });

    $("#input-afflict").autocomplete({
        source: [
            "Burned", "Frozen", "Paralysis", "Poisoned", "Bad Sleep", "Confused",
            "Cursed", "Disabled", "Rage", "Flinch", "Infatuation", "Sleep", "Suppressed", "Temporary Hit Points",
            "Fainted", "Blindness", "Total Blindness", "Slowed", "Stuck", "Trapped", "Tripped", "Vulnerable"
        ]
    });

    $("#btn-afflict").click(function () {
        var a = $("#input-afflict").val();

        // Update locally
        afflictions.push(a);
        updateAfflictions();

        // Update remotely
        sendMessage(host_id, JSON.stringify({
            "type": "pokemon_afflict_add",
            "pokemon": character_id,
            "affliction": a,
            "value": null
        }));
    });
});


// DISPLAY FUNCTIONS

/**
 * Display the imported Pokemon
 */
function displayInit() {
    var display = $("#tab1");

    $(".name").html(character_data["name"]);
    $(".level").html('Level ' + character_data['level']);
    $(".pokemon-image").attr("src", "img/pokemon/"+character_data["dex"]+".gif");

    //$("#dex-species").html('#' + character_data["dex"] + ' - Species');

    //Pokedex Data is set here
    $.getJSON("api/v1/pokemon/" + character_data['dex'], function (dex) {

        dex_data = dex;

        // -=-=-=-=-=-=-=-=-=-=-=-=-=-
        // Basic Pokemon information
        // -=-=-=-=-=-=-=-=-=-=-=-=-=-

        $("#DexData_Basic_ID").html(character_data["dex"]);
        $("#DexData_Basic_SpeciesName").html(dex.Species);

        //Sets the Pokemons Type on the Dex Screen.
        if (dex.Types.length == 1) {
            $("#DexData_Basic_Type1").html(dex.Types[0]); //Gets the pokemons Type
            $("#DexData_Basic_Type1").css("color", typeColor(dex.Types[0])); //Sets the Color of the Type
            $("#DexData_Basic_Type2").html(""); //Hide this if there is only one Type
            $("#DexData_Basic_TypeSep").html(""); //Hide this if there is only one Type
        } else {
            $("#DexData_Basic_Type1").html(dex.Types[0]); //Gets the pokemons 1st Type
            $("#DexData_Basic_Type1").css("color", typeColor(dex.Types[0])); //Sets the Color of the pokemons 1st Type
            $("#DexData_Basic_Type2").html(dex.Types[1]); //Gets the pokemons 2nd Type
            $("#DexData_Basic_Type2").css("color", typeColor(dex.Types[1])); //Sets the Color of the pokemons 2nd Type
        }
        $("#DexData_Basic_Weight").html(dex.Weight.WeightClass.Minimum + " -> " + dex.Weight.WeightClass.Maximum);
        $("#DexData_Basic_Size").html(dex.Height.Category.Minimum + " -> " + dex.Height.Category.Maximum);

        //Gets and Builds a string listing all the Diet Types (Seperated by a Comma)
        var DietString = "";
        for (var i = 0; i < dex.Environment.Diet.length; i++) {
            if (i != 0) {
                DietString = DietString + ", ";
            }
            DietString = DietString + dex.Environment.Diet[i];
        }
        //Gets and Builds a string listing all the Habitat Types (Seperated by a Comma)
        var HabString = "";
        for (var i = 0; i < dex.Environment.Habitats.length; i++) {
            if (i != 0) {
                HabString = HabString + ", ";
            }
            HabString = HabString + dex.Environment.Habitats[i];
        }
        //Applys the Habitats and Diet Data to the Dex Screen
        $("#DexData_Basic_Diet").html(DietString);
        $("#DexData_Basic_Habitats").html(HabString);

        // -=-=-=-=-=-=-=-=-=-=-=-=-=-
        // Base Stat information
        // -=-=-=-=-=-=-=-=-=-=-=-=-=-
        $("#DexData_Stats_HP").html(dex.BaseStats.HP);
        $("#DexData_Stats_Attack").html(dex.BaseStats.Attack);
        $("#DexData_Stats_Defense").html(dex.BaseStats.Defense);
        $("#DexData_Stats_SpAttack").html(dex.BaseStats.SpecialAttack);
        $("#DexData_Stats_SpDefense").html(dex.BaseStats.SpecialDefense);
        $("#DexData_Stats_Speed").html(dex.BaseStats.Speed);

        updateInfoPage();

        // -=-=-=-=-=-=-=-=-=-=-=-=-=-
        // Breeding information
        // -=-=-=-=-=-=-=-=-=-=-=-=-=-
        if (dex.BreedingData.HasGender) {
            $("#DexData_Breed_Male").html(dex.BreedingData.MaleChance * 100);
            $("#DexData_Breed_Female").html(dex.BreedingData.FemaleChance * 100);
        } else {
            $("#DexData_Breed_Male").html("0");
            $("#DexData_Breed_Female").html("0");
        }
        $("#DexData_Breed_HatchRate").html(dex.BreedingData.HatchRate + " Days");

        var EGString = "";
        for (var i = 0; i < dex.BreedingData.EggGroups.length; i++) {
            if (i != 0) {
                EGString = EGString + ", ";
            }
            EGString = EGString + dex.BreedingData.EggGroups[i];
        }
        $("#DexData_Breed_EggGroups").html(EGString);
        // -=-=-=-=-=-=-=-=-=-=-=-=-=-
        // Move Information
        // -=-=-=-=-=-=-=-=-=-=-=-=-=-
        // Levelup Moves
        // -=-=-=-=-=-=-=-=-=-=-=-=-=-

        for (var i = 0; i < dex.LevelUpMoves.length; i++) {
            (function (i) {
                $.getJSON("api/v1/moves/" + dex.LevelUpMoves[i].Name, function (Moves_dd) {
                    //Add New Row to the list containing Move Data
                    $("#DexData_Moves_LVup").append("<tr id='DexData_Move_LVU_" + i + "'> <td id='DexData_Move_LVU_" + i + "_Name'>" + dex.LevelUpMoves[i].Name + "</td> <td id='DexData_Move_LVU_" + i + "_Desc'>" + Moves_dd.Effect + "</td> <td id='DexData_Move_LVU_" + i + "_Type' style='color: " + typeColor(Moves_dd.Type) + "'>" + Moves_dd.Type + "</td> <td id='DexData_Move_LVU_" + i + "_Class'>" + Moves_dd.Class + "</td> <td id='DexData_Move_LVU_" + i + "_DB'>" + Moves_dd.DB + "</td> <td id='DexData_Move_LVU_" + i + "_AC'>" + Moves_dd.AC + "</td> <td id='DexData_Move_LVU_" + i + "_LV'> " + dex.LevelUpMoves[i].LevelLearned + " </td> </tr>");
                });
            })(i);
        }
        for (var i = 0; i < dex.TutorMoves.length; i++) {
            (function (i) {
                $.getJSON("api/v1/moves/" + dex.TutorMoves[i].Name, function (Moves_dd) {
                    //Add New Row to the list containing Move Data
                    $("#DexData_Moves_Tutor").append("<tr id='DexData_Move_Tutor_" + i + "'> <td id='DexData_Move_Tutor_" + i + "_Name'>" + dex.TutorMoves[i].Name + "</td> <td id='DexData_Move_Tutor_" + i + "_Desc'>" + Moves_dd.Effect + "</td> <td id='DexData_Move_Tutor_" + i + "_Type' style='color: " + typeColor(Moves_dd.Type) + "'>" + Moves_dd.Type + "</td> <td id='DexData_Move_Tutor_" + i + "_Class'>" + Moves_dd.Class + "</td> <td id='DexData_Move_Tutor_" + i + "_DB'>" + Moves_dd.DB + "</td> <td id='DexData_Move_Tutor_" + i + "_AC'>" + Moves_dd.AC + "</td> </tr>");
                });
            })(i);
        }
        for (var i = 0; i < dex.EggMoves.length; i++) {
            (function (i) {
                $.getJSON("api/v1/moves/" + dex.EggMoves[i].Name, function (Moves_dd) {
                    //Add New Row to the list containing Move Data
                    $("#DexData_Moves_Egg").append("<tr id='DexData_Move_Egg_" + i + "'> <td id='DexData_Move_Egg_" + i + "_Name'>" + dex.EggMoves[i].Name + "</td> <td id='DexData_Move_Egg_" + i + "_Desc'>" + Moves_dd.Effect + "</td> <td id='DexData_Move_Egg_" + i + "_Type' style='color: " + typeColor(Moves_dd.Type) + "'>" + Moves_dd.Type + "</td> <td id='DexData_Move_Egg_" + i + "_Class'>" + Moves_dd.Class + "</td> <td id='DexData_Move_Egg_" + i + "_DB'>" + Moves_dd.DB + "</td> <td id='DexData_Move_Egg_" + i + "_AC'>" + Moves_dd.AC + "</td> </tr>");
                });
            })(i);
        }
        for (var i = 0; i < dex.TmHmMoves.length; i++) {
            (function (i) {
                $.getJSON("api/v1/moves/" + dex.TmHmMoves[i].Name, function (Moves_dd) {
                    //Add New Row to the list containing Move Data
                    $("#DexData_Moves_TM").append("<tr id='DexData_Move_TM_" + i + "'> <td id='DexData_Move_TM_" + i + "_Name'>" + dex.TmHmMoves[i].Name + "</td> <td id='DexData_Move_TM_" + i + "_Desc'>" + Moves_dd.Effect + "</td> <td id='DexData_Move_TM_" + i + "_Type' style='color: " + typeColor(Moves_dd.Type) + "'>" + Moves_dd.Type + "</td> <td id='DexData_Move_TM_" + i + "_Class'>" + Moves_dd.Class + "</td> <td id='DexData_Move_TM_" + i + "_DB'>" + Moves_dd.DB + "</td> <td id='DexData_Move_TM_" + i + "_AC'>" + Moves_dd.AC + "</td> <td id='DexData_Move_TM_" + i + "_LV'>" + dex.TmHmMoves[i].TechnicalMachineId + "</td> </tr>");
                });
            })(i);
        }

        for (var i = 0; i < dex.Abilities.length; i++) {
            (function (i) {
                $.getJSON("api/v1/abilities/" + dex.Abilities[i].Name, function (Abilities_dd) {
                    //Add New Row to the list containing Abilitie Data
                    $("#DexData_Abilities").append("<tr id='DexData_Abilities_" + i + "'> <td id='DexData_Abilities_" + i + "_Name'>" + dex.TutorMoves[i].Name + "</td> <td id='DexData_Abilities_" + i + "_Effect'>" + Abilities_dd.Effect + "</td> <td id='DexData_Abilitie_" + i + "_Trigger'>" + Abilities_dd.Trigger + "</td> </tr>");
                });
            })(i);
        }

        for (var i = 0; i < dex.EvolutionStages.length; i++) {
            //Add New Row to the list containing Evolution Data
            $("#DexData_EvoForms").append("<tr id='DexData_Evolution_" + i + "'> <td><img src='/img/pokemon/" + species_to_dex(dex.EvolutionStages[i].Species) + ".gif'></td> <td id='DexData_Evolution_" + i + "_Species'>" + dex.EvolutionStages[i].Species + "</td> <th id='DexData_Evolution_" + i + "_Type'></th> <td id='DexData_Evolution_" + i + "_Stage'> " + dex.EvolutionStages[i].Stage + " </td> <td id='DexData_Evolution_" + i + "_Criteria'> " + dex.EvolutionStages[i].Criteria + " </td> </tr>");

            (function (i) {
                //Update Row to add Type Data
                $.getJSON("api/v1/pokemon/" + species_to_dex(dex.EvolutionStages[i].Species), function (Evo_dd) {
                    for (var t = 0; t < Evo_dd.Types.length; t++) {
                        if (t != 0) {
                            $("#DexData_Evolution_" + i + "_Type").append(" & ")
                        }
                        $("#DexData_Evolution_" + i + "_Type").append("<span style='color: " + typeColor(Evo_dd.Types[t]) + "'>" + Evo_dd.Types[t] + "</span>");
                    }
                });
            })(i);
        }
        for (var i = 0; i < dex.MegaEvolutions.length; i++) {
            //Add New Row to the list containing Mega Evolution Data
            $("#DexData_MegaForms").append("<tr id='DexData_Mega_" + i + "'> <td id='DexData_Mega_" + i + "_IMG'> </td>" +
                "<td id='DexData_Mega_" + i + "_Name'>" + dex.MegaEvolutions[i].Name + "</td>" +
                "<td id='DexData_Mega_" + i + "_Type'></td>" +
                "<td id='DexData_Mega_" + i + "_Abilitie'>" + dex.MegaEvolutions[i].Ability.Name + "</td>" +
                "<td id='DexData_Mega_" + i + "_HP'>" + (dex.MegaEvolutions[i].StatBonuses.HP + dex.BaseStats.HP) + "</td>" +
                "<td id='DexData_Mega_" + i + "_Attack'>" + (dex.MegaEvolutions[i].StatBonuses.Attack + dex.BaseStats.Attack) + "</td>" +
                "<td id='DexData_Mega_" + i + "_Defence'>" + (dex.MegaEvolutions[i].StatBonuses.Defense + dex.BaseStats.Defense) + "</td>" +
                "<td id='DexData_Mega_" + i + "_ApAttack'>" + (dex.MegaEvolutions[i].StatBonuses.SpecialAttack + dex.BaseStats.SpecialAttack) + "</td>" +
                "<td id='DexData_Mega_" + i + "_SpDefence'>" + (dex.MegaEvolutions[i].StatBonuses.SpecialDefense + dex.BaseStats.SpecialDefense) + "</td>" +
                "<td id='DexData_Mega_" + i + "_Speed'>" + (dex.MegaEvolutions[i].StatBonuses.Speed + dex.BaseStats.Speed) + "</td></tr>");

            //Adds the Image
            if (dex.MegaEvolutions[i].Name.toLowerCase() == "mega charizard x") {
                $("#DexData_Mega_" + i + "_IMG").html("<img src='/img/pokemon/006-mega-x.gif'>");
            } else if (dex.MegaEvolutions[i].Name.toLowerCase() == "mega charizard y") {
                $("#DexData_Mega_" + i + "_IMG").html("<img src='/img/pokemon/006-mega-y.gif'>");
            } else if (dex.MegaEvolutions[i].Name.toLowerCase() == "mega mewtwo x") {
                $("#DexData_Mega_" + i + "_IMG").html("<img src='/img/pokemon/150-mega-x.gif'>");
            } else if (dex.MegaEvolutions[i].Name.toLowerCase() == "mega mewtwo y") {
                $("#DexData_Mega_" + i + "_IMG").html("<img src='/img/pokemon/150-mega-y.gif'>");
            } else {
                $("#DexData_Mega_" + i + "_IMG").html("<img src='/img/pokemon/" + species_to_dex(dex.Species) + "-mega.gif'>");
            }

            //Update Row to add Type Data
            for (var t = 0; t < dex.MegaEvolutions[i].Types.length; t++) {
                if (t != 0) {
                    $("#DexData_Mega_" + i + "_Type").append(" & ")
                }
                $("#DexData_Mega_" + i + "_Type").append("<span style='color: " + typeColor(dex.MegaEvolutions[i].Types[t]) + "'>" + dex.MegaEvolutions[i].Types[t] + "</span>")
            }
        }
    });

    //Speed Stat is set here
    $("#speed").html(character_data["speed"]);

    $.each(moves_data, function (i, move) {

        var moveBtn = $('<a class="btn btn-raised btn-move">\n' +
            '                    <div>\n' +
            '                        <h4 class="move-name"></h4>\n' +
            '                        <span class="pull-right">\n' +
            '                                <span class="label label-warning label-type"></span>\n' +
            '                            </span>\n' +
            '                    </div>\n' +
            '                    <div class="btn-move-footer">\n' +
            '                            <span class="pull-right move-desc" data-toggle="tooltip" data-placement="left">\n' +
            '                                <i class="material-icons">info</i>\n' +
            '                            </span>\n' +
            '                        <span class="move-freq"></span>\n' +
            '                    </div>\n' +
            '                </a>');

        moveBtn.css("display", "block").attr("data-index", i);

        var title = move["Title"];
        var freq = move["Freq"];
        var type = move["Type"];
        var desc = move["Effect"];
        var dmgType = move["Class"];

        // Get color for type
        var color = typeColor(move["Type"]);

        // Get icon for frequency

        var frqIco;

        if (freq === "EOT")
            frqIco = '<i class="material-icons">autorenew</i> Every Other Turn';
        else if (freq === "At-Will")
            frqIco = '<i class="material-icons">grade</i> At Will';
        else if (freq === "Static")
            frqIco = '<i class="material-icons">remove</i> Static';
        else if (freq.indexOf("Scene") !== -1 || freq.indexOf("Daily") !== -1)
            frqIco = '<i class="material-icons">alarm</i> ' + freq;
        else
            frqIco = '<i class="material-icons">fiber_manual_record</i> ' + freq;

        // Put data on card

        moveBtn.find(".move-name").html(title).css("color", color);
        moveBtn.find(".label-type").html(type).css("background-color", color);
        moveBtn.find(".move-freq").html(frqIco);
        moveBtn.find(".move-desc").attr("data-content", dmgType + " Move. " + desc);

        $(".moves").append(moveBtn);
    });

    $('[data-toggle="tooltip"]').tooltip();

    display.find(".new-form").css("display", "none");
    display.find(".pokemon-enemy").css("display", "block");

    updateStatus();

    $(".btn-move").click(function () {
        var move = moves_data[parseInt($(this).attr("data-index"))];
        currentMove = move;

        // Populate fields
        $(".modal-move .move-desc").html(move["Effect"]);
        $(".modal-move .move-name").html(move["Title"]).css("color", $(this).find(".move-name").css("color"));
        $(".modal-move #move-type").val(move["Type"]).parent().removeClass("is-empty");
        $(".modal-move #move-class").val(move["Class"]).parent().removeClass("is-empty");

        if (move["DB"])
            $(".modal-move #move-db").removeAttr("disabled").val(move["DB"]);
        else
            $(".modal-move #move-db").attr("disabled", "").val("");

        if (move["AC"])
            $(".modal-move #move-ac").removeAttr("disabled").val(move["AC"]);
        else
            $(".modal-move #move-ac").attr("disabled", "").val("");

        if (move["Range"])
            $(".modal-move #move-range").val(move["Range"]);
        else
            $(".modal-move #move-range").val("");

        if (move["Crits On"])
            $(".modal-move #move-crit").removeAttr("disabled").val(move["Crits On"]);
        else
            $(".modal-move #move-crit").attr("disabled", "").val("");

        updateTargetList();

        $('#modalTarget').modal('show');
    });
}

function onTargetGridLoaded() {
    $(".grid-piece").click(function () {
        if ($(this).hasClass("active")) {
            $(this).removeClass("active");
            $(".grid[data-x='"+$(this).attr("data-x")+"'][data-y='"+$(this).attr("data-y")+"']").removeClass("active");
        }
        else {
            $(this).addClass("active");
            $(".grid[data-x='"+$(this).attr("data-x")+"'][data-y='"+$(this).attr("data-y")+"']").addClass("active");
        }
    });
}

function updateInfoPage() {
    // Chart stuff
    var dataCompletedTasksChart = {
        labels: ['HP', 'ATK', 'DEF', 'SPATK', 'SPDEF', 'SPD'],
        series: [
            [dex_data.BaseStats.HP, dex_data.BaseStats.Attack, dex_data.BaseStats.Defense, dex_data.BaseStats.SpecialAttack,
                dex_data.BaseStats.SpecialDefense, dex_data.BaseStats.Speed]
        ]
    };

    var optionsChart = {
        lineSmooth: Chartist.Interpolation.cardinal({
            tension: 0
        }),
        low: 0,
        high: 15,
        chartPadding: { top: 0, right: 0, bottom: 0, left: 0}
    };

    var completedTasksChart = new Chartist.Bar('#graphBaseStats', dataCompletedTasksChart, optionsChart);

    // start animation for the Completed Tasks Chart - Line Chart
    md.startAnimationForBarChart(completedTasksChart);
}

function updateStatus() {
    var max_hp = character_data['level'] + character_data['hp'] * 3 + 10;

    $(".bar-hp").css("width", Math.floor((parseInt(character_data['health']) / max_hp) * 100) + "%");
    $(".bar-exp").css("width", Math.floor((parseInt(character_data['EXP']) / EXP_CHART[parseInt(character_data['level'])]) * 100) + "%");
}

function updateTargetList() {
    var html = '<button class="btn btn-danger btn-lg" data-target="other">Other Target</button>';

    $.each(battle_data, function (id, name) {
        html += '<button class="btn btn-danger btn-lg" data-target="' + id + '">' + name + '</button>';
    });

    $("#select-target-body").html(html);

    $("#select-target-body .btn").click(function () {

        // Populate move with modified data
        if (currentMove["Type"])     currentMove["Type"]  = $("#move-type").val();
        if (currentMove["Class"])    currentMove["Class"] = $("#move-class").val();

        if (currentMove["DB"])       currentMove["DB"] = parseInt($("#move-db").val());
        if (currentMove["AC"])       currentMove["AC"] = parseInt($("#move-ac").val());

        if (currentMove["Crits On"]) currentMove["Crits On"] = parseInt($("#move-crit").val());

        sendMessage(host_id, JSON.stringify({
            "type": "battle_move",
            "dealer": character_id,
            "target": $(this).attr("data-target"),
            "move": currentMove,
            "bonus_dmg": $("#move-dmg-bonus").val(),
            "bonus_acc": $("#move-acc-bonus").val()
        }));

        currentMove = null;

        $('#modalTarget').modal('hide');
    });
}

/**
 * Renders the afflictions and handles actions
 */
function updateAfflictions() {
    // Table start
    var html = '<table class="table">' +
        '<thead>' +
        '<tr>' +
        '<th>Name</th>' +
        '<th class="text-right">Actions</th>' +
        '</tr>' +
        '</thead>' +
        '<tbody>';

    // Add elements
    $.each(afflictions, function (key, affliction) {
        html += '<tr>' +
            '<td class="center-vertical">' + affliction + '</td>' +
            '<td class="td-actions text-right" data-target="affliction" data-value="' + affliction + '">' +
            '<button type="button" rel="tooltip" title="Trigger Affliction" class="btn btn-info btn-simple btn-xs btn-trigger">' +
            '<i class="material-icons">play_arrow</i>' +
            '</button>' +
            '<button type="button" rel="tooltip" title="Remove Affliction" class="btn btn-danger btn-simple btn-xs btn-delete">' +
            '<i class="material-icons">close</i>' +
            '</button>' +
            '</td>' +
            '</tr>';
    });

    // Close table
    html += '</tbody></table>';

    // Update table
    $("#afflictions").html(html);

    // Add listeners
    $("#afflictions .btn-trigger").click(function () {
        sendMessage(host_id, JSON.stringify({
            "type": "pokemon_afflict_trigger",
            "pokemon": character_id,
            "affliction": $(this).parent().attr("data-value")
        }));
    });
    $("#afflictions .btn-delete").click(function () {
        sendMessage(host_id, JSON.stringify({
            "type": "pokemon_afflict_delete",
            "pokemon": character_id,
            "affliction": $(this).parent().attr("data-value")
        }));

        afflictions.splice(afflictions.indexOf($(this).parent().attr("data-value")), 1);
        updateAfflictions();
    });

    $('[rel="tooltip"]').tooltip();
}


// SERVER FUNCTIONS

peer.on('connection', function (c) {
    receiveMessages(c, readMessage);

    window.alert("debug");
});

function onClickConnect() {
    host_id = $("#gm-id").val();

    if (peer.connections[host_id] != null) {
        for (var i = 0; i < peer.connections[host_id].length; i++) {
            peer.connections[host_id][i].close();
            peer.connections[host_id].splice(i, 1);
        }
    }

    connection = peer.connect(host_id, {
        label: 'chat',
        serialization: 'none',
        metadata: {message: 'connect to host'}
    });

    connection.on('open', function () {
        receiveMessages(connection, readMessage);

        // Connection established - make appropriate calls

        if ($.isEmptyObject(character_data)) {
            connection.send(JSON.stringify({
                "type": "pokemon_list"
            }));
        } else {
            connection.send(JSON.stringify({
                "type": "reconnect",
                "character": character_data["ID"]
            }));
        }
    });
    connection.on('error', function (err) {
        alert(err);
    });
}

function readMessage(connection, data) {
    var json = JSON.parse(data);

    /*
     Pokemon received
     */
    if (json.type == "pokemon") {
        character_data = json.pokemon;
        fetchMoves();
    }
    /*
     List of Pokemon received
     */
    else if (json.type == "pokemon_list") {
        var html = "";
        $.each(json.pokemon, function (id, pmon) {
            html += '<option value="' + id + '">' + pmon['name'] + '</option>';
        });
        $("#pokemonId").html(html);

        $("#init-select").css("display", "block");
        $("#init-connect").css("display", "none");
    }
    /*
     Pokemon Added to Battle
     */
    else if (json.type == "battle_added") {
        battle_data[json.pokemon_id] = json.pokemon_name;
        updateTargetList();

        // Show target list
        $("#modalTarget-join").addClass("hidden");
        $("#modalTarget-select").removeClass("hidden");
    }
    /*
     Battle ended
     */
    else if (json.type == "battle_end") {
        // Hide target list
        $("#modalTarget-join").removeClass("hidden");
        $("#modalTarget-select").addClass("hidden");
    }
    /*
     Grid returned
     */
    else if (json.type == "battle_grid") {
        $(".battle-grid").html(json.html);
        onTargetGridLoaded();
    }
    /*
     Health changed
     */
    else if (json.type == "health") {
        character_data["health"] = json.value;
        updateStatus();
    }
    /*
     Battle data changed
     */
    else if (json.type == "data_changed") {
        if (json.field == "affliction") {
            afflictions = json.value;
            updateAfflictions();
        }
        else {
            $("#" + json.field).val(json.value);
        }
    }
    /*
     Add affliction
     */
    else if (json.type == "afflict_add") {
        // Check if affliction is already on Pokemon
        if ($.inArray(json.affliction) < 0) {
            afflictions.push(json.affliction);
            updateAfflictions();
        }
    }
    /*
     Remove affliction
     */
    else if (json.type == "afflict_delete") {
        // Check if affliction is on Pokemon
        if ($.inArray(json.affliction) >= 0) {
            afflictions.splice(array.indexOf(json.affliction), 1);
            updateAfflictions();
        }
    }
    /*
     Snackbar Alert Received
     */
    else if (json.type == "alert") {
        doToast(message["content"]);
    }

}

function onClickLoadFromSelected() {
    character_id = $("#pokemonId").val();

    sendMessage(host_id, JSON.stringify({
        "type": "pokemon_get",
        "from": client_id,
        "pokemon_id": character_id
    }));
}

function addPokemonToBattle() {

    var message = {
        "type": "battle_add",
        "from": client_id,
        "pokemon": character_id,
        "stage_atk": $("#stage-atk").val(),
        "stage_def": $("#stage-def").val(),
        "stage_spatk": $("#stage-spatk").val(),
        "stage_spdef": $("#stage-spdef").val(),
        "stage_speed": $("#stage-speed").val(),
        "stage_acc": $("#stage-acc").val(),
        "stage_eva": $("#stage-eva").val()
    };

    sendMessage(host_id, JSON.stringify(message));
}

function fetchMoves() {
    $.getJSON("/api/v1/moves/?names=" + encodeURIComponent(JSON.stringify(character_data["moves"])), function (json) {
        var i = 0;
        $.each(json, function (name, move) {
            if (name != "") {
                move["Title"] = name;

                moves_data[i] = move;
            }

            i++;
        });

        displayInit();

        $(".content-init").css("display", "none");
        $(".content-main").css("display", "block");
    });
}


// MANAGEMENT FUNCTIONS

function setStage(field, attr) {
    var stage = parseInt(field.val());

    var mon = $("#tab1");

    if (stage < -6) {
        stage = -6;
        field.val("-6");
    }
    else if (stage > 6) {
        stage = 6;
        field.val("6");
    }

    if (stage == -6)
        mon.attr(attr, "0.4");
    else if (stage == -5)
        mon.attr(attr, "0.5");
    else if (stage == -4)
        mon.attr(attr, "0.6");
    else if (stage == -3)
        mon.attr(attr, "0.7");
    else if (stage == -2)
        mon.attr(attr, "0.8");
    else if (stage == -1)
        mon.attr(attr, "0.9");
    else if (stage == 0)
        mon.attr(attr, "1.0");
    else if (stage == 1)
        mon.attr(attr, "1.2");
    else if (stage == 2)
        mon.attr(attr, "1.4");
    else if (stage == 3)
        mon.attr(attr, "1.6");
    else if (stage == 4)
        mon.attr(attr, "1.8");
    else if (stage == 5)
        mon.attr(attr, "2.0");
    else if (stage == 6)
        mon.attr(attr, "2.2");
}

function damage(dmg, moveType, isSpecial) {
    moveType = moveType.toLocaleLowerCase();

    var progress = $(".progress");
    var mon = $("#tab1");

    var maxHp = parseInt(progress.attr("data-max-hp"));
    var hp = parseInt(progress.attr("data-hp"));

    var def, defStage;

    if (isSpecial) {
        def = parseInt(mon.attr("data-spdef"));
        defStage = parseFloat(mon.attr("data-spdef-stage"));
    }
    else {
        def = parseInt(mon.attr("data-def"));
        defStage = parseFloat(mon.attr("data-def-stage"));
    }

    var damage = Math.floor(dmg - (def * defStage));

    var monType1 = mon.attr("data-type-1");
    var monType2 = mon.attr("data-type-2");

    var effect1 = 1, effect2 = 1;

    $.getJSON("/api/v1/types", function (json) {
        effect1 = json[moveType][monType1];

        if (monType2 != null)
            effect2 = json[moveType][monType2];

        damage = damage * effect1 * effect2;

        if (damage < 1)
            damage = 1;

        hp = hp - damage;

        if (hp <= 0) {
            window.alert("The pokemon fainted!");
        }

        progress.attr("data-hp", hp);

        sendUpdate("hp", hp);

        $(".progress-bar").css("width", Math.floor((hp / maxHp) * 100) + "%");
    });
}

$.getJSON("/api/v1/types", function (json) {
    $.each(json, function (k, v) {
        document.getElementById("dmg-type").innerHTML += "<option>" + k + "</option>";
    })
});


// VIEW MANAGEMENT

/**
 * Called when the navbar menu icon is hit
 */
function onClickMenu() {
    var elem = $(".sidebar-nav");

    if (elem.css("display").substring(0, 4) == "none")
        elem.css("display", "block", "important").removeClass("hidden-xs").addClass("hidden-sm");
    else
        elem.css("display", "none").addClass("hidden-xs").addClass("hidden-sm");
}

$(".btn-sidebar, .navbar-fixed-bottom .btn").click(function () { //TODO: redo sidebar
    var tab = $(this).attr("data-target");

    // Change out tab content
    $(".tab").css("display", "none");
    $("#tab" + tab).css("display", "block");

    // Change navbar
    $(this).parent().parent().find(".active").removeClass("active");
    $(this).parent().addClass("active");

    if (tab == 2)
        updateInfoPage();
    else if (tab == 3)
        updateAfflictions();
});

$('#modalTargetDEBUG').on('shown.bs.modal', function () {
    sendMessage(host_id, JSON.stringify({
        'type': 'battle_grid',
        //'max_width': $("#select-target-body").width()
        'max_width': 500
    }));

    $('.battle-grid').html('<div class="loading-holder"><div class="loading-50px"></div></div>');
});

// UTILITY FUNCTIONS

function numToType(num) {
    switch (num) {
        case 1 :
            return "bug";
        case 2 :
            return "dark";
        case 3 :
            return "dragon";
        case 4 :
            return "electric";
        case 5 :
            return "fairy";
        case 6 :
            return "fighting";
        case 7 :
            return "fire";
        case 8 :
            return "flying";
        case 9 :
            return "ghost";
        case 10 :
            return "grass";
        case 11 :
            return "ground";
        case 12 :
            return "ice";
        case 13 :
            return "normal";
        case 14 :
            return "poison";
        case 15 :
            return "psychic";
        case 16 :
            return "rock";
        case 17 :
            return "steel";
        case 18 :
            return "water";
    }
}

function typeToNum(type) {
    switch (type) {
        case "bug":
            return 1;
        case "dark":
            return 2;
        case "dragon":
            return 3;
        case "electric":
            return 4;
        case "fairy":
            return 5;
        case "fighting":
            return 6;
        case "fire":
            return 7;
        case "flying":
            return 8;
        case "ghost":
            return 9;
        case "grass":
            return 10;
        case "ground":
            return 11;
        case "ice":
            return 12;
        case "normal":
            return 13;
        case "poison":
            return 14;
        case "psychic":
            return 15;
        case "rock":
            return 16;
        case "steel":
            return 17;
        case "water":
            return 18;
    }
}
