<?php

class Spendings_Controller extends Base_Controller {

	/*
	|--------------------------------------------------------------------------
	| The Default Controller
	|--------------------------------------------------------------------------
	|
	| Instead of using RESTful routes and anonymous functions, you might wish
	| to use controllers to organize your application API. You'll love them.
	|
	| This controller responds to URIs beginning with "home", and it also
	| serves as the default controller for the application, meaning it
	| handles requests to the root of the application.
	|
	| You can respond to GET requests to "/home/profile" like so:
	|
	|		public function action_profile()
	|		{
	|			return "This is your profile!";
	|		}
	|
	| Any extra segments are passed to the method as parameters:
	|
	|		public function action_profile($id)
	|		{
	|			return "This is the profile for user {$id}.";
	|		}
	|
	*/
        public $do = '';
        public $od = '';

	public function action_index()
	{
        return Redirect::to('spendings/zoznam');
	}


    //Adriana Gogoľáková - 25/03/2013
    public function action_zoznam()
    {

       $view = View::make('spendings.zoznam')
            ->with('active', 'vydavky')->with('subactive', 'spendings/zoznam')->with('secretword', md5(Auth::user()->t_heslo));
        
        //Dátum
        $view->do = '';
        
        $view->od = '';
        
        //Osoby - nákupcovia
        $view->osoby = DB::table('D_OSOBA')->where('id_domacnost', '=',Auth::user()->id)->get();
        $view->message = Session::get('message');

       if (empty($view->osoby)) {
            $view = View::make('spendings.message')
                ->with('active', 'vydavky')->with('subactive', 'spendings/zoznam')->with('secretword', md5(Auth::user()->t_heslo));
            $view->message = "Nebola vytvorená žiadna osoba";
            return $view;
        }

        foreach ($view->osoby as $osoba)
        {
            $id_osob[] = $osoba->id;
            $id_domov[] = $osoba->id_domacnost;     
        }
              
        //Výdavky: Vydavok (VIEW_F_VYDAVOK)
        $view->vydavky = Vydavok::where_in('id_osoba',$id_osob)->order_by('d_datum', 'DESC')->get();

        //Obchodní partneri - príjemcovia
        $view->obch_partneri = DB::table('D_OBCHODNY_PARTNER') ->where('id_domacnost', '=',Auth::user()->id)->get();

        
        //Typy výdavkov
        $view->typyV = DB::table('D_TYP_VYDAVKU')->where('id_domacnost', '=',Auth::user()->id)->get();
        
       /* $id = Input::get('id');
        $editovany_zaznam = Kategoria::where('id','=',$id)->get();
        //Kategorie
        $view->kategorie = Kategoria::where('id', 'LIKE','%K%')->where('id_domacnost','=',Auth::user()->id)->get();*/
       
        return $view;
    }


    //Adriana Gogoľáková - 25/03/2013
    public function action_filter()
    {

         $view = View::make('spendings.zoznam')
            ->with('active', 'vydavky')->with('subactive', 'spendings/zoznam')->with('secretword', md5(Auth::user()->t_heslo));
        
        //Osoba - všetky osoby
        $view->osoby = DB::table('D_OSOBA')->where('id_domacnost', '=',Auth::user()->id)->get();
        foreach ($view->osoby as $osoba)
        {
            $id_osob[] = $osoba->id;
        }
        $id = Input::get('id');

        //Položky výdavku
        $view->polozky = DB::query("select
                                      a.id,
                                      concat(
                                    case when a.typ =  'K' then concat(space(length(a.id_kategoria)-4), substr(a.id_kategoria, 4))
                                    else space(length(a.id_kategoria)-4)
                                    end,
                                    ' ',
                                    a.nazov
                                    ) nazov
                                    from
                                    (
                                    select
                                    kategoria.id id,
                                    kategoria.id id_kategoria,
                                    kategoria.t_nazov nazov,
                                    kategoria.fl_typ typ
                                    from D_KATEGORIA_A_PRODUKT kategoria
                                    where kategoria.fl_typ = 'K'
                                    and kategoria.id_domacnost = ". Auth::user()->id ."

                                    union all

                                    select
                                    produkt.id id,
                                    produkt.id_kategoria_parent id_kategoria,
                                    produkt.t_nazov nazov,
                                    produkt.fl_typ typ
                                    from D_KATEGORIA_A_PRODUKT produkt
                                    where produkt.fl_typ = 'P'
                                    and produkt.id_domacnost = ". Auth::user()->id ."
                                    ) a
                                    order by a.id_kategoria,a.typ
                                   ");

        $id = Input::get('id');
        $view->polozky_vydavku = DB::table('R_VYDAVOK_KATEGORIA_A_PRODUKT')->where('id_vydavok','=', $id)->get();

        //Dátum
        $od = Input::get('od');
        $od = ($od!='') ? date('Y-m-d',strtotime($od)) : '';

        $do = Input::get('do');
        $do = ($do!='') ? date('Y-m-d',strtotime($do)) : date('Y-m-d');

        
        //Typy výdavkov
        $view->typyV = DB::table('D_TYP_VYDAVKU')
        ->where('id_domacnost', '=',Auth::user()->id)
        ->get();
        //Výdavky
        $view->vydavky = Vydavok::where_in('id_osoba',$id_osob)->where('d_datum', '>=', $od)->where('d_datum', '<=', $do)->order_by('d_datum', 'DESC');

        //Obchodný partner - prijemca
        $view->obch_partneri = DB::table('D_OBCHODNY_PARTNER') 
        ->where('id_domacnost', '=',Auth::user()
        ->id)->get();

        
        //Filter podľa obchodného partnera
        $prijemca = Input::get('partner');
        if ($prijemca != 'all') 
        $view->vydavky->where("id_obchodny_partner",'=',$prijemca);


        //Filter podľa osoby
        $osoba = Input::get('osoba');
        if($osoba !='all')
        $view->vydavky->where("id_osoba",'=',$osoba);

        //Filter podľa typu výdavku
        $typ = Input::get('typ');
        if($typ !='all')
        $view->vydavky->where("id_typ_vydavku",'=',$typ);
        
        $view->do = $do;
        $view->od = $od;
        $view->vydavky = $view->vydavky->get();
        return $view;
    }


    public function action_periodicalspending()
    {
        $view = View::make('spendings.periodicalspending')
            ->with('active', 'vydavky')
            ->with('subactive', 'spendings/periodicalspending')
            ->with('secretword', md5(Auth::user()->t_heslo));

        $view->errors = Session::get('errors');
        $view->error = Session::get('error');

        $view->message = Session::get('message');
        
        $view->osoby = DB::table('D_OSOBA')->where('id_domacnost', '=',Auth::user()->id)->get();
        foreach ($view->osoby as $osoba)
        {
        	$id_osob[] = $osoba->id;
        }

        $view->datum = date("Y-m-d");
        
        $view->sablony = DB::query("select v.id,v.id_obchodny_partner,v.t_poznamka,v.fl_pravidelny,vkp.id_kategoria_a_produkt,vkp.vl_jednotkova_cena,op.t_nazov as prijemca,kp.t_nazov as kategoria,o.t_meno_osoby,o.t_priezvisko_osoby,tv.t_nazov_typu_vydavku ".
                "from F_VYDAVOK v, R_VYDAVOK_KATEGORIA_A_PRODUKT vkp, D_OBCHODNY_PARTNER op, D_KATEGORIA_A_PRODUKT kp, D_TYP_VYDAVKU tv, D_OSOBA o ".
                "where v.id = vkp.id_vydavok and v.id_obchodny_partner = op.id and vkp.id_kategoria_a_produkt = kp.id and v.id_typ_vydavku = tv.id and v.id_osoba = o.id and v.fl_sablona = 'A' and v.id_osoba in (".implode(",", $id_osob).")");
        
        return $view;
    }
    
    public function action_simplespending()
    {
      
        $id = Input::get('id');
        //if (!isset($id)) $id = Session::get('id');
        $subactive = 'spendings/jednoduchyvydavok';

        if (!isset($id))
        {
            $view = View::make('spendings.vydavok-novy')
                ->with('active', 'vydavky')->with('subactive', $subactive)->with('secretword', md5(Auth::user()->t_heslo));
        }else
        {
            $view = View::make('spendings.vydavok-editacia')
                ->with('active', 'vydavky')->with('subactive', $subactive)->with('secretword', md5(Auth::user()->t_heslo));
            $view->vydavky = Vydavok::where('id', '=', $id);
            $view->polozky_vydavku = DB::table('R_VYDAVOK_KATEGORIA_A_PRODUKT')->where('id_vydavok','=', $id)->get();
            $view->celkova_suma = 0;
            foreach ($view->polozky_vydavku as $polozka_vydavku)
            {
                $view->celkova_suma += $polozka_vydavku->vl_jednotkova_cena * $polozka_vydavku->num_mnozstvo;
                if ($polozka_vydavku->fl_typ_zlavy == 'A') $view->celkova_suma -= $polozka_vydavku->vl_zlava;
                if ($polozka_vydavku->fl_typ_zlavy == 'P') $view->celkova_suma -= ($polozka_vydavku->vl_jednotkova_cena * $polozka_vydavku->num_mnozstvo) * $polozka_vydavku->vl_zlava/100;

            }
            $view->vydavky = $view->vydavky->get();
            if ($view->vydavky[0]->fl_typ_zlavy == 'A') $view->celkova_suma -= $view->vydavky[0]->vl_zlava;
            if ($view->vydavky[0]->fl_typ_zlavy == 'P') $view->celkova_suma -= $view->celkova_suma * $view->vydavky[0]->vl_zlava/100;



        }

        $view->osoby = DB::table('D_OSOBA')->where('id_domacnost', '=',Auth::user()->id)->get();
        foreach ($view->osoby as $osoba)
        {
            $id_osob[] = $osoba->id;
        }

        $view->polozky = DB::query("select
                                      a.id,
                                      concat(
                                    case when a.typ =  'K' then concat(space(length(a.id_kategoria)-4), substr(a.id_kategoria, 4))
                                    else space(length(a.id_kategoria)-4)
                                    end,
                                    ' ',
                                    a.nazov
                                    ) nazov,
                                    a.cena
                                    from
                                    (
                                    select
                                    kategoria.id id,
                                    kategoria.id id_kategoria,
                                    kategoria.t_nazov nazov,
                                    kategoria.fl_typ typ,
                                    kategoria.vl_zakladna_cena cena
                                    from D_KATEGORIA_A_PRODUKT kategoria
                                    where kategoria.fl_typ = 'K'
                                    and kategoria.id_domacnost = ". Auth::user()->id ."

                                    union all

                                    select
                                    produkt.id id,
                                    produkt.id_kategoria_parent id_kategoria,
                                    produkt.t_nazov nazov,
                                    produkt.fl_typ typ,
                                    produkt.vl_zakladna_cena cena
                                    from D_KATEGORIA_A_PRODUKT produkt
                                    where produkt.fl_typ = 'P'
                                    and produkt.id_domacnost = ". Auth::user()->id ."
                                    ) a
                                    order by a.id_kategoria,a.typ
                                   ");

        $view->dzejson = Response::json($view->polozky);
        
        $view->partneri = Partner::where('id_domacnost','=',Auth::user()->id)->get();
        
        $view->message = Session::get('message');

        $view->typy_vydavkov = DB::table('D_TYP_VYDAVKU')->where('id_domacnost','=',Auth::user()->id)->get();
        
        return $view;
    }

    public function action_savespending()
    {
        $data = Input::All() ;
        $data_for_sql['id_osoba'] = $data['osoba'];
        $data_for_sql['id_obchodny_partner'] =  $data['partner'];            
        $data_for_sql['d_datum'] =  date('Y-m-d',strtotime($data['datum']));
        $data_for_sql['t_poznamka'] =  $data['poznamka'];
        $data_for_sql['id_typ_vydavku'] =  $data['typ-vydavku'];
        $data_for_sql['vl_zlava'] =  intval($data['celkova-zlava']);
        $data_for_sql['fl_typ_zlavy'] =  $data['celkovy-typ-zlavy'];

        if (isset($data['update']))
        {
            /*
             * UPDATE HLAVICKY
             */

            $aktualizacia = DB::table('F_VYDAVOK')
                ->where('id', '=', $data['hlavicka-id'])
                ->update($data_for_sql);

          /*
           * UPDATE POLOZIEK
           */
            for ( $i = 0 ;$i < count($data['vydavok-id']);$i++)
            {
              $polozky_for_sql['id_kategoria_a_produkt'] = $data['polozka-id'][$i];
              $polozky_for_sql['vl_jednotkova_cena'] = floatval(str_replace(',', '.',$data['cena'][$i]));
              $polozky_for_sql['num_mnozstvo'] = intval($data['mnozstvo'][$i]);
              $polozky_for_sql['vl_zlava'] = floatval($data['zlava'][$i]);
              $polozky_for_sql['fl_typ_zlavy'] = $data['typ-zlavy'][$i];

           //vydavok sa nerovna N bude sa updatovat
            if ($data['vydavok-id'][$i] != 'N')
            {
                // Aktualizovanie položiek
                DB::table('R_VYDAVOK_KATEGORIA_A_PRODUKT')
                 ->where('id', '=', $data['vydavok-id'][$i])
                 ->update($polozky_for_sql);
            }

           // vydavok sa rovna N bude nova polozka
            if ($data['vydavok-id'][$i] == 'N')
            {
                
                $polozky_for_sql['id_vydavok'] = $data['hlavicka-id'];
                DB::table('R_VYDAVOK_KATEGORIA_A_PRODUKT')->insert($polozky_for_sql);
            }
              unset($polozky_for_sql);
            }
            //return Redirect::to_action('spendings@simplespending?id='.$data['hlavicka-id'])->with("message", 'Výdavok bol aktualizovaný');
            return Redirect::to('spendings/zoznam')
                        ->with('message', 'Výdavok bol úspešne aktualizovaný')
                        ->with('status_class','sprava-uspesna');

        /*
         * INSERT NOVEHO VYDAVKU
         */
        } else{
                $data_for_sql['fl_sablona'] =  'N';
                $data_for_sql['fl_pravidelny'] =  'N';

                $idvydavku = DB::table('F_VYDAVOK')
                       ->insert_get_id($data_for_sql);

                
                for ( $i = 0 ;$i < count($data['vydavok-id']);$i++)
                    {
                        $polozky_for_sql['id_kategoria_a_produkt'] = $data['polozka-id'][$i];
                        $polozky_for_sql['vl_jednotkova_cena'] =floatval(str_replace(',', '.',$data['cena'][$i]));
                        $polozky_for_sql['num_mnozstvo'] = intval($data['mnozstvo'][$i]);
                        $polozky_for_sql['vl_zlava'] = floatval($data['zlava'][$i]);
                        $polozky_for_sql['fl_typ_zlavy'] = $data['typ-zlavy'][$i];
                       
                        $polozky_for_sql['id_vydavok'] = $idvydavku;        
                        DB::table('R_VYDAVOK_KATEGORIA_A_PRODUKT')->insert($polozky_for_sql);
                        unset($polozky_for_sql);
                    }

                return Redirect::to('spendings/zoznam')
                        ->with('message', 'Výdavok bol úspešne pridaný')
                        ->with('status_class','sprava-uspesna');

                }

     }


    public function action_deletepolozka()
    {
        $secretword = md5(Auth::user()->t_heslo);
        $pol = Input::get('pol');
       DB::query('DELETE FROM R_VYDAVOK_KATEGORIA_A_PRODUKT WHERE CONCAT(md5(id),\''.$secretword.'\') = \''.$pol.'\'');

        return Redirect::to_action('spendings@simplespending?id='.Input::get('vydavokid'))
                            ->with("message", 'Položka bola vymazaná')
                            ->with('status_class','sprava-uspesna');
    }


    public function action_deletespending()
    {
        $secretword = md5(Auth::user()->t_heslo);
        $vydavok_id = Input::get('vydavok');
        DB::query('DELETE FROM R_VYDAVOK_KATEGORIA_A_PRODUKT WHERE CONCAT(md5(id_vydavok),\''.$secretword.'\') = \''.$vydavok_id.'\''); //mazanie poloziek
        DB::query('DELETE FROM F_VYDAVOK WHERE CONCAT(md5(id),\''.$secretword.'\') = \''.$vydavok_id.'\''); //mazanie hlavicky
       
        return Redirect::to('spendings/zoznam')
            ->with('message', 'Výdavok bol vymazaný')
            ->with('status_class','sprava-uspesna');
    }
    

    public function action_multideletespending()
    {
    	$secretword = md5(Auth::user()->t_heslo);
    	$vydavok_ids = Input::get('vydavok');
    	if (is_array($vydavok_ids))
    	{
    		foreach ($vydavok_ids as $vydavok_id)
    		{
    			DB::query('DELETE FROM R_VYDAVOK_KATEGORIA_A_PRODUKT WHERE CONCAT(md5(id_vydavok),\''.$secretword.'\') = \''.$vydavok_id.'\''); //mazanie poloziek
    			DB::query('DELETE FROM F_VYDAVOK WHERE CONCAT(md5(id),\''.$secretword.'\') = \''.$vydavok_id.'\''); //mazanie hlavicky
    		}
    	}
    	return Redirect::to('spendings/zoznam')
                ->with('message', 'Výdavky boli vymazané')
                ->with('status_class','sprava-uspesna');
    }
    

    public function action_sablona() {
    	
        $idecko_z_ulozenia = Session::get('id');

    	$id = Input::get('id');
        
    	if (isset($id) || isset($idecko_z_ulozenia)) 
        {
    		
    		$view = View::make('spendings.sablona-editacia')->with('active', 'vydavky')->with('subactive', 'spendings/sablona');
    		
            if (isset($idecko_z_ulozenia)) 
                {   
                    $id = $idecko_z_ulozenia;
                }

    		$view->message = Session::get('message');
    		
    		$view->osoby = DB::table('D_OSOBA')->where('id_domacnost', '=',Auth::user()->id)->get();
    		foreach ($view->osoby as $osoba)
    		{
    			$id_osob[] = $osoba->id;
    		}
    		
        	$view->polozky = DB::query("select
                                      a.id,
                                      concat(
                                    case when a.typ =  'K' then concat(space(length(a.id_kategoria)-4), substr(a.id_kategoria, 4))
                                    else space(length(a.id_kategoria)-4)
                                    end,
                                    ' ',
                                    a.nazov
                                    ) nazov
                                    from
                                    (
                                    select
                                    kategoria.id id,
                                    kategoria.id id_kategoria,
                                    kategoria.t_nazov nazov,
                                    kategoria.fl_typ typ
                                    from D_KATEGORIA_A_PRODUKT kategoria
                                    where kategoria.fl_typ = 'K'
                                    and kategoria.id_domacnost = ". Auth::user()->id ."

                                    union all

                                    select
                                    produkt.id id,
                                    produkt.id_kategoria_parent id_kategoria,
                                    produkt.t_nazov nazov,
                                    produkt.fl_typ typ
                                    from D_KATEGORIA_A_PRODUKT produkt
                                    where produkt.fl_typ = 'P'
                                    and produkt.id_domacnost = ". Auth::user()->id ."
                                    ) a
                                    order by a.id_kategoria,a.typ
                                   ");

            $view->partneri = Partner::where('id_domacnost','=',Auth::user()->id)->get();
    		
    		$view->editovana_sablona = DB::query("SELECT v.id,v.id_obchodny_partner,v.t_poznamka,v.fl_pravidelny,vkp.id_kategoria_a_produkt,vkp.vl_jednotkova_cena,op.t_nazov AS prijemca,kp.t_nazov AS kategoria, v.id_osoba, v.id_typ_vydavku " .
    				"FROM F_VYDAVOK v, R_VYDAVOK_KATEGORIA_A_PRODUKT vkp, D_OBCHODNY_PARTNER op, D_KATEGORIA_A_PRODUKT kp ".
    				"WHERE v.id = vkp.id_vydavok AND v.id_obchodny_partner = op.id AND vkp.id_kategoria_a_produkt = kp.id AND v.fl_sablona = 'A' AND v.id_osoba in (".implode(",", $id_osob).") AND v.id = '".$id."'");
    		
    		$view->typy_vydavkov = DB::table('D_TYP_VYDAVKU')->where('id_domacnost','=',Auth::user()->id)->get();

    		return $view;
    		
    	} else {
    		
    		$view = View::make('spendings.sablona-nova')->with('active', 'vydavky')->with('subactive', 'spendings/sablona');
    		 
    		$view->message = Session::get('message');
    		 
    		$view->osoby = DB::table('D_OSOBA')->where('id_domacnost', '=',Auth::user()->id)->get();
    		foreach ($view->osoby as $osoba)
    		{
    			$id_osob[] = $osoba->id;
    		}
    		 
    		$view->polozky = DB::query("select
                                      a.id,
                                      concat(
                                    case when a.typ =  'K' then concat(space(length(a.id_kategoria)-4), substr(a.id_kategoria, 4))
                                    else space(length(a.id_kategoria)-4)
                                    end,
                                    ' ',
                                    a.nazov
                                    ) nazov
                                    from
                                    (
                                    select
                                    kategoria.id id,
                                    kategoria.id id_kategoria,
                                    kategoria.t_nazov nazov,
                                    kategoria.fl_typ typ
                                    from D_KATEGORIA_A_PRODUKT kategoria
                                    where kategoria.fl_typ = 'K'
                                    and kategoria.id_domacnost = ". Auth::user()->id ."
   
                                    union all
   
                                    select
                                    produkt.id id,
                                    produkt.id_kategoria_parent id_kategoria,
                                    produkt.t_nazov nazov,
                                    produkt.fl_typ typ
                                    from D_KATEGORIA_A_PRODUKT produkt
                                    where produkt.fl_typ = 'P'
                                    and produkt.id_domacnost = ". Auth::user()->id ."
                                    ) a
                                    order by a.id_kategoria,a.typ
                                   ");
    		 
    		$view->partneri = Partner::where('id_domacnost','=',Auth::user()->id)->get();
    		
            $view->typy_vydavkov = DB::table('D_TYP_VYDAVKU')->where('id_domacnost','=',Auth::user()->id)->get();

                //Výdavky: Vydavok (VIEW_F_VYDAVOK)
            $view->vydavky = Vydavok::where_in('id_osoba',$id_osob)->order_by('d_datum', 'DESC')->get();
              
    		return $view;
    		
    	}
    }

    public function action_ulozsablonu() {
    	
    	$data = Input::All();

        // $data_for_sql sa zapíše do F_VYDAVOK:
    	$data_for_sql['t_poznamka'] =  $data['nazov'];
    	$data_for_sql['id_obchodny_partner'] =  $data['partner'];
    	$data_for_sql['fl_pravidelny'] =  $data['pravidelnost'];
        $data_for_sql['id_osoba'] =  $data['osoba'];
        $data_for_sql['id_typ_vydavku'] =  $data['typ-vydavku'];
    	
    	$polozky_for_sql['id_kategoria_a_produkt'] = $data['polozka-id'];
    	$polozky_for_sql['vl_jednotkova_cena'] = $data['hodnota'];
    	
        if (empty($data_for_sql['t_poznamka'])) {  
          $errors['nazov'] = 'Zadajte prosím názov šablóny';
        }

        if (!empty($errors)) {
          $error = 'Opravte chyby vo formulári';
          
            if (isset($data['hlavicka-id'])) // Editácia
                {
                    $view = Redirect::to('spendings/sablona')
                                        ->with('error', $error)
                                        ->with('errors',$errors)
                                        ->with('id',$data['hlavicka-id']);     
                } 
                 else 
                    { // Nový záznam
                     $view = Redirect::to('spendings/sablona')
                                        ->with('error', $error)
                                        ->with('errors',$errors);
                    }

            return $view;
        }

        // Aktualizácia šablóny:
    	if (isset($data['update'])) {
    		
    		$aktualizacia = DB::table('F_VYDAVOK')
    		->where('id', '=', $data['hlavicka-id'])
    		->update($data_for_sql);
    		
    		DB::table('R_VYDAVOK_KATEGORIA_A_PRODUKT')
    		->where('id_vydavok', '=', $data['hlavicka-id'])
    		->update($polozky_for_sql);
    		
    		return Redirect::to('spendings/sablona')
                ->with('message', 'Šablóna bola úspešne zmenená')
                ->with('status_class','sprava-uspesna');
    		
    	} 
        // Pridanie novej šablóny:
            else {
        		$data_for_sql['fl_sablona'] = 'A';
        		
            /* 
        		$osoby = DB::table('D_OSOBA')->where('id_domacnost', '=',Auth::user()->id)->get();
        		$data_for_sql['id_osoba'] = $osoby[0]->id; 
            */
        		$idvydavku = DB::table('F_VYDAVOK')->insert_get_id($data_for_sql);
        		
        		$polozky_for_sql['id_vydavok'] = $idvydavku;
        		
        		$idvydavku2 = DB::table('R_VYDAVOK_KATEGORIA_A_PRODUKT')->insert($polozky_for_sql);
        		
        		return Redirect::to('spendings/periodicalspending')
                    ->with('message', 'Šablóna bola úspešne pridaná')
                    ->with('status_class','sprava-uspesna');
        		
        	}
    	
    }
    
    public function action_zmazsablonu() {
    	
    	$secretword = md5(Auth::user()->t_heslo);
    	$vydavok_id = Input::get('sablona');
    	DB::query('DELETE FROM R_VYDAVOK_KATEGORIA_A_PRODUKT WHERE CONCAT(md5(id_vydavok),\''.$secretword.'\') = \''.$vydavok_id.'\''); //mazanie poloziek
    	DB::query('DELETE FROM F_VYDAVOK WHERE CONCAT(md5(id),\''.$secretword.'\') = \''.$vydavok_id.'\''); //mazanie hlavicky
    	
        return Redirect::to('spendings/periodicalspending')
                ->with('message', 'Šablóna bola vymazaná')
                ->with('status_class','sprava-uspesna');
    	
    }
    
    public function action_zmazsablony() {
    	
    	$secretword = md5(Auth::user()->t_heslo);
    	$vydavok_ids = Input::get('sablona');
    	if (is_array($vydavok_ids))
    	{
    		foreach ($vydavok_ids as $vydavok_id)
    		{
		    	DB::query('DELETE FROM R_VYDAVOK_KATEGORIA_A_PRODUKT WHERE CONCAT(md5(id_vydavok),\''.$secretword.'\') = \''.$vydavok_id.'\''); //mazanie poloziek
		    	DB::query('DELETE FROM F_VYDAVOK WHERE CONCAT(md5(id),\''.$secretword.'\') = \''.$vydavok_id.'\''); //mazanie hlavicky
    		}
    	}
    	return Redirect::to('spendings/periodicalspending')
                ->with('message', 'Šablóny boli vymazané')
                ->with('status_class','sprava-uspesna');
    	
    }

    public function action_savefromtemplate() {
    	
    	$data = Input::All();

        if (($data['sablona']) == 'nic') 
        {

          $errors['nazov'] = 'Zadajte prosím názov šablóny';
        
          $error = 'Nevybral si žiadnu šablónu';
          
          $view = Redirect::to('spendings/periodicalspending')
                    ->with('error', $error)
                    ->with('errors',$errors);

            return $view;
        }
    	
    	$sablony = DB::query("select v.id,v.id_obchodny_partner,v.t_poznamka,v.fl_pravidelny,vkp.id_kategoria_a_produkt,vkp.vl_jednotkova_cena " .
    			"from F_VYDAVOK v, R_VYDAVOK_KATEGORIA_A_PRODUKT vkp ".
    			"where v.id = vkp.id_vydavok and v.id = ".$data['sablona']);
    	
    	$data_for_sql['id_osoba'] = $data['osoba'];
    	$data_for_sql['id_obchodny_partner'] = $sablony[0]->id_obchodny_partner;
    	$data_for_sql['d_datum'] = date('Y-m-d',strtotime($data['datum']));
    	$data_for_sql['t_poznamka'] = $sablony[0]->t_poznamka;
    	$data_for_sql['fl_pravidelny'] = $sablony[0]->fl_pravidelny;
    	$data_for_sql['vl_zlava'] = 0;
    	$data_for_sql['fl_typ_zlavy'] = '0';
    	$data_for_sql['fl_sablona'] = 'N';
    	
    	$idvydavku = DB::table('F_VYDAVOK')->insert_get_id($data_for_sql);
    	
    	$polozky_for_sql['id_kategoria_a_produkt'] = $sablony[0]->id_kategoria_a_produkt;
    	$polozky_for_sql['vl_jednotkova_cena'] = $data['suma'];
    	$polozky_for_sql['num_mnozstvo'] = 1;
    	$polozky_for_sql['vl_zlava'] = 0;
    	$polozky_for_sql['fl_typ_zlavy'] = '0';
    	$polozky_for_sql['id_vydavok'] = $idvydavku;
    	
    	DB::table('R_VYDAVOK_KATEGORIA_A_PRODUKT')->insert($polozky_for_sql);
    	
    	return Redirect::to('spendings/zoznam')
                    ->with('message', 'Výdavok bol úspešne pridaný')
                    ->with('status_class','sprava-uspesna');
    	
    }


    public function  getVydavok($id){
        return null;

    }


    public function action_pridajdodavatela()
    {
        $data_for_sql['id_osoba'] = Input::get('osoba');
        $data_for_sql['t_nazov'] = Input::get('nazov-partnera');
        $data_for_sql['t_adresa'] = Input::get('adresa');
        DB::table('D_OBCHODNY_PARTNER')
            ->insert_get_id($data_for_sql);
        return Redirect::to('spendings/pridanie')
                ->with('message', 'Partner bol pridaný')
                ->with('status_class','sprava-uspesna');

    }


    public function action_vyber_cenu_pre_produkt() {

        $id=$_GET['id'];

        $suma = DB::query("SELECT vl_zakladna_cena AS cena
                           FROM D_KATEGORIA_A_PRODUKT 
                           WHERE id= '".$id."'");

        return $suma[0]->cena;

    }


    public function action_vyber_cenu_pre_sablonu() {

        $id=$_GET['id'];

        $suma = DB::query("SELECT vkp.vl_jednotkova_cena AS cena
                FROM F_VYDAVOK v, R_VYDAVOK_KATEGORIA_A_PRODUKT vkp, D_OBCHODNY_PARTNER op, D_KATEGORIA_A_PRODUKT kp, D_TYP_VYDAVKU tv, D_OSOBA o 
                WHERE v.id = vkp.id_vydavok and v.id_obchodny_partner = op.id and vkp.id_kategoria_a_produkt = kp.id and v.id_typ_vydavku = tv.id and v.id_osoba = o.id and v.fl_sablona = 'A' 
                    AND v.id= '".$id."'");

        return $suma[0]->cena;

    }


     public function action_vyber_osobu_pre_sablonu() {

        $id=$_GET['id'];

        $osoba = DB::query("SELECT o.id AS id, o.t_meno_osoby AS meno, o.t_priezvisko_osoby AS priezvisko
                FROM F_VYDAVOK v, R_VYDAVOK_KATEGORIA_A_PRODUKT vkp, D_OBCHODNY_PARTNER op, D_KATEGORIA_A_PRODUKT kp, D_TYP_VYDAVKU tv, D_OSOBA o 
                WHERE v.id = vkp.id_vydavok and v.id_obchodny_partner = op.id and vkp.id_kategoria_a_produkt = kp.id and v.id_typ_vydavku = tv.id and v.id_osoba = o.id and v.fl_sablona = 'A' 
                    AND v.id= '".$id."'");

        /*$vysledok['meno'] = $osoba[0]->meno;
        $vysledok['priezvisko'] = $osoba[0]->priezvisko;
        return $vysledok;*/

        $meno = $osoba[0]->meno;
        $priezvisko = $osoba[0]->priezvisko;
        $id = $osoba[0]->id;

        echo json_encode(array("meno"=>$meno,
                               "priezvisko"=>$priezvisko,
                               "id"=>$id
                        ));

    }




}
