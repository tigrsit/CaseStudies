<?php

class Reporting_Controller extends Base_Controller {

public function action_index() 
	{
        $active='reporting';
        echo $active;
        return Redirect::to('reporting/report_vydavky')->with('active', 'ciselniky');
	}


public function action_report_vydavky() {

        $view = View::make('reporting.report-vydavky')
            ->with('active', 'reporting')
            ->with('subactive','reporting/vydavky');

      $level = Input::get('level');
      $zaciatok = Input::get('od');
      $koniec = Input::get('do');
      $zobrazovanie = Input::get('zobrazovanie');

      if (!isset($zobrazovanie)) $zobrazovanie = 'celkove';

      // Takýto formát mi treba do DB: = '"2013-03-31"';

        if (isset($zaciatok)) 
            { 
                $zaciatok = date('Y-m-d', strtotime($zaciatok));
            }
        
            else 
                {   // V prípade že nie je zadaný chcem selektovať od úplneho začiatku - hodnota bude 1970-01-01
                   $zaciatok = date('Y-m-d', strtotime($zaciatok)); 
                }


        if (isset($koniec)) 
            { 
                $koniec = date('Y-m-d', strtotime($koniec));  
            }
        
            else 
                {
                    // Toto zbehne iba pri prvom zobrazení alebo ak sa vymaže celé pole do
                    $today = date("Y-m-d");
                    $koniec = $today;   
                }
            
     
        $view->select1 = DB::query("SELECT dkap.t_nazov, dkap.id_kategoria_parent, sum(`vl_jednotkova_cena` * `num_mnozstvo`) as suma_vydavkov
                                    FROM F_VYDAVOK fv
                                    join R_VYDAVOK_KATEGORIA_A_PRODUKT rv
                                    join D_KATEGORIA_A_PRODUKT dkap
                                    join D_DOMACNOST dd
                                    WHERE fv.id = rv.id_vydavok and dkap.id = rv.id_kategoria_a_produkt and dd.id = dkap.id_domacnost = ". Auth::user()->id ." and dkap.id_domacnost and dkap.id_kategoria_parent IS NULL
                                    and d_datum >=  '".$zaciatok."' AND d_datum <=  '".$koniec."'
                                    GROUP BY dkap.t_nazov, dkap.id, dkap.id_kategoria_parent"); 


        $view->select2 = DB::query("SELECT MONTHNAME(d_datum) as mesiac, dkap.t_nazov as nazov_kategorie, sum(`vl_jednotkova_cena` * `num_mnozstvo`) as suma_vydavkov
                                    FROM F_VYDAVOK fv
                                    join R_VYDAVOK_KATEGORIA_A_PRODUKT rv
                                    join D_KATEGORIA_A_PRODUKT dkap
                                    join D_DOMACNOST dd
                                    WHERE fv.id = rv.id_vydavok and dkap.id = rv.id_kategoria_a_produkt and dd.id = dkap.id_domacnost and dkap.id_domacnost = ". Auth::user()->id ." and dkap.id_kategoria_parent IS NULL and MONTHNAME(d_datum) IS NOT NULL
                                    and d_datum >=  '".$zaciatok."' AND d_datum <=  '".$koniec."'
                                    GROUP BY MONTH(d_datum), dkap.t_nazov");

        $view->vsetkykategorie = DB::query("SELECT t_nazov
                                            FROM D_KATEGORIA_A_PRODUKT
                                            WHERE id_kategoria_parent IS NULL
                                            AND id_domacnost = ". Auth::user()->id ." ");

        if ($zaciatok == '1970-01-01') $zaciatok='';    // kvôli tomu, aby fungoval datepicker vo view
        
        return $view->with('zaciatok',$zaciatok)
                    ->with('koniec',$koniec)
                    ->with('zobrazovanie',$zobrazovanie);

    }


    public function action_report_prijmy(){

		$viewData = array(
			'typy'			=> Prijem::get_typy(),
			'persons'		=> Prijem::get_person(),
			);

		$view = View::make('reporting.report-prijmy',$viewData)
					->with('active', 'reporting')
					->with('subactive','reporting/prijmy');

		return $view;
	}


















}

?>