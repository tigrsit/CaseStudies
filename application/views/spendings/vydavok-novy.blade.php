@include('head')

<script>    var js_polozky = {{ $dzejson }} </script>
<h3 style="color: #bc4348;">{{ $message }}  </h3>

@include('spendings/sp-submenu')


{{ Form::open('spendings/savespending', 'POST', array('class' => 'side-by-side')); }}
<input type="hidden" name="hlavicka-id" id="hidden" value="N"/>

<div class="thumbnail">
    <h4>Parametre:</h4>

    <div class="input-prepend" style="float:left;width:295px">
        <span class="add-on">   Dátum:            </span>
        <input name="datum" class="span3 datepicker" type="text" placeholder="Deň.Mesiac.Rok" value="{{ date('d.m.Y') }}">
    </div>

    <div class="input-prepend" style="float:left;width:350px">
        <span class="add-on">   Partner:          </span>
        <select name="partner" class="span3">
            @foreach ($partneri as $partner)
            <option value="{{ $partner->id }}"> {{ $partner->t_nazov }} </option>
            @endforeach
        </select>
    </div>

    <div class="input-prepend">
        <span class="add-on">   Zaplatila osoba:   </span>
        <select name="osoba" class="span2">
            @foreach ($osoby as $osoba)
            <option value="{{ $osoba->id }}" > {{ $osoba->t_meno_osoby }} {{$osoba->t_priezvisko_osoby }}</option>
            @endforeach
        </select>
    </div>

    <div class="input-prepend">
        <span class="add-on">   Typ výdavku:   </span>
        <select name="typ-vydavku" class="span2">
            @foreach ($typy_vydavkov as $typ)
            <option value="{{ $typ->id }}" > {{ $typ->t_nazov_typu_vydavku }} </option>
            @endforeach
        </select>
    </div>

    <div class="input-prepend">
        <span class="add-on">   Poznámka:   </span>
        <input name="poznamka" class="span10" type="text" value="">
    </div>

     <HR>

    <h4>Položky výdavku</h4>

    <table id="tbl-vydavky" class="table table-bordered" >
        <thead>
            <tr>
                <th>                        </th>
                <th>    Položka             </th>
                <th>    Cena /ks (bez zľavy)</th>
                <th>    Počet               </th>
                <th>    Zľava               </th>
            </tr>
        </thead>
        <input type="hidden" name="vydavok-id[]" id="hidden" value="N"/>
        <tr>
            <td><a class="btn" href=""><i class="icon-remove"></i></a></td>
            <td>
                <select name="polozka-id[]" class="span4" style="font-family: Courier, 'Courier New', monospace;" >
                    @foreach ($polozky as $polozka)
                    <option value="{{ $polozka->id }}"> {{ str_replace(" ", "&nbsp;",$polozka->nazov); }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <div class="input-append">
                    <input name="cena[]" class="span2" type="text" value="" />
                    <span class="add-on">€</span>
                </div>
            </td>
            <td>
                <div class="input-append">
                    <input name="mnozstvo[]" class="span1" type="text" value="1" />
                    <span class="add-on">m.j.</span>
                </div>
            </td>
            <td>
                <input name="zlava[]" class="span1" type="text" value="0" />
                <select name="typ-zlavy[]" class="span2">
                    <option value="0" > Bez zľavy   </option>
                    <option value="P">  Zľava v %   </option>
                    <option value="A">  Zľava v EUR </option>
                </select>
            </td>
        </tr>
    </table>

    <button type="button" class="btn btn-primary" onclick="pridaj_riadok_do_vydavkov()">
        <i class=" icon-edit icon-white"></i>
            Pridaj položku
    </button>
   
     <HR>

    <h4> Celková zľava   </h4>
        <div class="input-prepend" style="float:left; width:185px;">
            <span class="add-on">Hodnota zľavy: </span>
            <input name="celkova-zlava" class="span1" type="text" value="0" />
        </div>

        <div class="input-prepend">
            <span class="add-on">Typ zľavy: </span>
            <select name="celkovy-typ-zlavy" class="span2">
                <option value="0" >Bez zľavy</option>
                <option value="P" >Zľava v %</option>
                <option value="A" >Zľava v EUR</option>
            </select>
        </div>
       
        <div class="input-prepend">
            <span class="add-on">Celková suma: </span>
            <input class="span3" type="text"  disabled="disabled">
        </div>

        <div class="input-prepend">
            <span class="add-on">Celková zľava: </span>
            <input class="span3" type="text" disabled="disabled">
        </div>
        
{{ Form::close() }}
    
    <HR>

     <button type="submit" class="btn btn-danger">
        <i class=" icon-edit icon-white"></i>
            Ulož výdavok
    </button>

</div>


@include('foot')