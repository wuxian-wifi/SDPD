<?php

namespace App\Controller;

use App\Entity\IFG_SDPD\Operator;
use PhpParser\Node\Scalar\MagicConst\File;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use phpseclib\Net\SFTP;
use phpseclib\Net\SSH2;

/**
 * Compare controller.
 *
 * @Route("/compare")
 */

class CompareController extends Controller
{

    /**
     * @Route("/correction/{codegreffe}&{codestatut}&{chrono}&{millesime}&{numacte}&{numdepot}&{siren}", name="correctionpage")
     */
    public function correction(Request $request, $codegreffe, $codestatut, $chrono, $millesime, $numacte, $numdepot, $siren)
    {
        $url_GED = "https://INFOGREFFE:JsE2=BDC@services.infogreffe.fr/wwwDemat/getDocument?codegreffe=$codegreffe&codestatut=$codestatut&chrono=$chrono&millesime=$millesime&numeroacte=$numacte&numerodepot=$numdepot&typeproduit=act&telecharge";
        //$url_GED = "https://qual.infogreffe.fr/wwwDemat/getDocument?codegreffe=$codegreffe&codestatut=$codestatut&chrono=$chrono&millesime=$millesime&numeroacte=$numacte&numerodepot=$numdepot&typeproduit=act&telecharge";

        $filename = ($this->generateUniqueFileName()).".pdf";

        $pdfContent = file_get_contents($url_GED);
        file_put_contents($this->getParameter('files')."/".$filename ,$pdfContent);

        $apis = $this->data_API($siren);
        $infos_db = $this->data_TEST2($siren);
        $infos_operator = $this->data_Operator($request, $siren);

        return $this->render('compare/compare.html.twig', [
            'filename' => $filename,
            'apis' => $apis,
            'siren' => $siren,
            'infos_db' => $infos_db,
            'infos_operator' => $infos_operator
        ]);
    }

    /**
     * @Route("/generateXML/{filename}&{operator}&{nb_BE}", name="generateXMLpage")
     */
    public function generateXML($filename, $operator, $nb_BE)
    {
        $fileSystem = new Filesystem();
        $file_pdf = ($this->getParameter('files'))."/".$filename;

        if (file_exists($file_pdf)) {
            $fileSystem->remove($file_pdf);
        }

        $denomination_sociale = $_POST['denomination_sociale_1'];
        $siren = $_POST['siren_1'];
        $immatriculation = $_POST['immatriculation_1'];
        $forme_juridique = $_POST['forme_juridique_1'];
        $adresse_sociale = $_POST['adresse_sociale_1'];
        $code_postal_sociale = $_POST['code_postal_sociale_1'];
        $commune_sociale = $_POST['commune_sociale_1'];
        $pays_sociale = $_POST['pays_sociale_1'];
        $civilite = array();
        $nom_naissance = array();
        $nom_usage = array();
        $pseudonyme = array();
        $prenom_principal = array();
        $prenom_autres = array();
        $naissance_date = array();
        $naissance_lieu = array();
        $departement_pays = array();
        $nationalite = array();
        $adresse_domicile = array();
        $code_postal_domicile = array();
        $commune_domicile = array();
        $pays_domicile = array();
        $detention_capital_directe = array();
        $detention_capital_indirecte = array();
        $detention_droits_directe = array();
        $detention_droits_indirecte = array();
        $pourcentage_capital = array();
        $pourcentage_droits = array();
        $autre_moyen_case = array();
        $exercice = array();
        $representant = array();
        $date_effect = array();


        foreach($_POST as $key => $value) {
            if (strpos($key, 'civilite_') === 0) {
                array_push($civilite, $value);
            }
            if (strpos($key, 'nom_naissance_') === 0) {
                array_push($nom_naissance, $value);
            }
            if (strpos($key, 'nom_usage_') === 0) {
                array_push($nom_usage, $value);
            }
            if (strpos($key, 'pseudonyme_') === 0) {
                array_push($pseudonyme, $value);
            }
            if (strpos($key, 'prenom_principal_') === 0) {
                array_push($prenom_principal, $value);
            }
            if (strpos($key, 'prenom_autres_') === 0) {
                array_push($prenom_autres, $value);
            }
            if (strpos($key, 'naissance_date_') === 0) {
                array_push($naissance_date, $value);
            }
            if (strpos($key, 'naissance_lieu_') === 0) {
                array_push($naissance_lieu, $value);
            }
            if (strpos($key, 'departement_pays_') === 0) {
                array_push($departement_pays, $value);
            }
            if (strpos($key, 'nationalite_') === 0) {
                array_push($nationalite, $value);
            }
            if (strpos($key, 'adresse_domicile_') === 0) {
                array_push($adresse_domicile, $value);
            }
            if (strpos($key, 'code_postal_domicile_') === 0) {
                array_push($code_postal_domicile, $value);
            }
            if (strpos($key, 'commune_domicile_') === 0) {
                array_push($commune_domicile, $value);
            }
            if (strpos($key, 'pays_domicile_') === 0) {
                array_push($pays_domicile, $value);
            }
            if (strpos($key, 'detention_capital_directe_') === 0) {
                array_push($detention_capital_directe, $value);
            }
            if (strpos($key, 'detention_capital_indirecte_') === 0) {
                array_push($detention_capital_indirecte, $value);
            }
            if (strpos($key, 'detention_droits_directe_') === 0) {
                array_push($detention_droits_directe, $value);
            }
            if (strpos($key, 'detention_droits_indirecte_') === 0) {
                array_push($detention_droits_indirecte, $value);
            }
            if (strpos($key, 'pourcentage_capital_') === 0) {
                array_push($pourcentage_capital, $value);
            }
            if (strpos($key, 'pourcentage_droits_') === 0) {
                array_push($pourcentage_droits, $value);
            }
            if (strpos($key, 'autre_moyen_case_') === 0) {
                array_push($autre_moyen_case, $value);
            }
            if (strpos($key, 'exercice_') === 0) {
                array_push($exercice, $value);
            }
            if (strpos($key, 'representant_') === 0) {
                array_push($representant, $value);
            }
            if (strpos($key, 'date_effect_') === 0) {
                array_push($date_effect, $value);
            }
        }

        $infos_xml = array();

        $civilite = $this->infos_check($nb_BE, $civilite);
        $nom_naissance = $this->infos_check($nb_BE, $nom_naissance);
        $nom_usage = $this->infos_check($nb_BE, $nom_usage);
        $pseudonyme = $this->infos_check($nb_BE, $pseudonyme);
        $prenom_principal = $this->infos_check($nb_BE, $prenom_principal);
        $prenom_autres = $this->infos_check($nb_BE, $prenom_autres);
        $naissance_date = $this->infos_check($nb_BE, $naissance_date);
        $naissance_lieu = $this->infos_check($nb_BE, $naissance_lieu);
        $departement_pays = $this->infos_check($nb_BE, $departement_pays);
        $nationalite = $this->infos_check($nb_BE, $nationalite);
        $adresse_domicile = $this->infos_check($nb_BE, $adresse_domicile);
        $code_postal_domicile = $this->infos_check($nb_BE, $code_postal_domicile);
        $commune_domicile = $this->infos_check($nb_BE, $commune_domicile);
        $pays_domicile = $this->infos_check($nb_BE, $pays_domicile);
        $pourcentage_capital = $this->infos_check($nb_BE, $pourcentage_capital);
        $pourcentage_droits = $this->infos_check($nb_BE, $pourcentage_droits);
//        $autre_moyen_case = $this->infos_check($nb_BE, $autre_moyen_case);
        $exercice = $this->infos_check($nb_BE, $exercice);
        $date_effect = $this->infos_check($nb_BE, $date_effect);

        $code_rejet = $_POST['code_rejets'];
        $detention_capital = $this->detention_capital_check($nb_BE, $detention_capital_directe, $detention_capital_indirecte);
        $detention_droits = $this->detention_droits_check($nb_BE, $detention_droits_directe, $detention_droits_indirecte);


        for($i = 0; $i < $nb_BE; $i++) {
            array_push($infos_xml, array(
                $i => array(
                    'denomination_sociale' => $denomination_sociale,
                    'siren' => $siren,
                    'immatriculation' => $immatriculation,
                    'forme_juridique' => $forme_juridique,
                    'adresse_sociale' => $adresse_sociale,
                    'code_postal_sociale' => $code_postal_sociale,
                    'commune_sociale' => $commune_sociale,
                    'pays_sociale' => $pays_sociale,
                    'civilite' => $civilite[$i],
                    'nom_naissance' => $nom_naissance[$i],
                    'nom_usage' => $nom_usage[$i],
                    'pseudonyme' => $pseudonyme[$i],
                    'prenom_principal' => $prenom_principal[$i],
                    'prenom_autres' => $prenom_autres[$i],
                    'naissance_date' => $naissance_date[$i],
                    'naissance_lieu' => $naissance_lieu[$i],
                    'departement_pays' => $departement_pays[$i],
                    'nationalite' => $nationalite[$i],
                    'adresse_domicile' => $adresse_domicile[$i],
                    'code_postal_domicile' => $code_postal_domicile[$i],
                    'commune_domicile' => $commune_domicile[$i],
                    'pays_domicile' => $pays_domicile[$i],
                    'detention_capital' => $detention_capital[$i],
                    'pourcentage_capital' => $pourcentage_capital[$i],
                    'detention_droits' => $detention_droits[$i],
                    'pourcentage_droits' => $pourcentage_droits[$i],
                    'autre_moyen_case' => $autre_moyen_case[$i],
                    'exercice' => $exercice[$i],
                    'representant' => $representant[$i],
                    'date_effect' => $date_effect[$i],
                    'code_rejet' => $code_rejet
                )
            ));
        }

        /**
         * Opérateur
         */
        $operator_db = new Operator();
        $em_SDPD = $this->getDoctrine()->getManager('default');

        date_default_timezone_set("Europe/Paris");
        $dateSaisie_operator = date_create(date('Y-m-d H:i:s'));

        $operator_db->setSiren($siren);
        $operator_db->setOperator($operator);
        $operator_db->setDateSaisie($dateSaisie_operator);

        $em_SDPD->persist($operator_db);
        $em_SDPD->flush();

        /**
         * Générer XML
         */
        $infos_db = $this->data_TEST2($siren);
        $res_xml = $this->XML_generator($nb_BE, $infos_xml, $operator);
        $date_saisie = date('Ymd' ,strtotime($infos_db['dtsaisie']));
        $file_name = "beffectifs_".$siren."_".$date_saisie.".xml";
        $files_route = ($this->getParameter('files'))."/".$file_name;

        $file_xml = fopen($files_route, "w");
        fwrite($file_xml, $res_xml);
        fclose($file_xml);

        /**
         * Send the XML file to OVH
         */
        $sftp = new SFTP('79.137.30.194');
        $sftp_login = $sftp->login('infogreffe', '3Mg0Fs2Eg2');
        $remote_route = '/home/infogreffe/saisies'.'/'.$file_name;
        if (!$sftp_login) {
            throw new \Exception('Cannot login into your server !');
        } else {
            $sftp->put($remote_route, $files_route, SFTP::SOURCE_LOCAL_FILE);
            return $this->render('compare/downloadXML.html.twig', [
                'file_name' => $file_name
            ]);
        }
    }

    /**
     * XML
     */
    private function XML_generator($nb_BE, $infos_xml, $operator)
    {
        $siren = $this->siren_traite($infos_xml[0][0]['siren']);
        $apis = $this->data_API($siren);
        $infos_db = $this->data_TEST2($siren);

        date_default_timezone_set("Europe/Paris");
        $date_saisie = date('Y-m-d');

        $xw = xmlwriter_open_memory();
        xmlwriter_set_indent($xw, 1);
        $res = xmlwriter_set_indent_string($xw, '');

        xmlwriter_start_document($xw, '1.0', 'UTF-8');
            xmlwriter_start_element($xw, 'listeSaisiesBE');
                xmlwriter_start_element($xw, 'VersionSchema');
                xmlwriter_text($xw, '1');
                xmlwriter_end_element($xw);

                xmlwriter_start_element($xw, 'operateur');
                xmlwriter_text($xw, $operator);
                xmlwriter_end_element($xw);

                xmlwriter_start_element($xw, 'SaisieBE');
                    xmlwriter_start_element($xw, 'dateDemande');
                    xmlwriter_text($xw, $infos_db['dtdemande']);
                    xmlwriter_end_element($xw);

                    xmlwriter_start_element($xw, 'numeroDemande');
                    xmlwriter_text($xw, $infos_db['iddemext']);
                    xmlwriter_end_element($xw);

                    xmlwriter_start_element($xw, 'typeDemande');
                    xmlwriter_text($xw, $infos_db['typedemande']);
                    xmlwriter_end_element($xw);

                    xmlwriter_start_element($xw, 'typeDeclaration');
                    xmlwriter_text($xw, $this->typeDeclaration_traite($apis['declaration']['type']));
                    xmlwriter_end_element($xw);

                    xmlwriter_start_element($xw, 'identificationEntreprise');
                        xmlwriter_start_element($xw, 'denomination');
                        xmlwriter_text($xw, $infos_xml[0][0]['denomination_sociale']);
                        xmlwriter_end_element($xw);

                        xmlwriter_start_element($xw, 'siren');
                        xmlwriter_text($xw, $siren);
                        xmlwriter_end_element($xw);

                        xmlwriter_start_element($xw, 'codeGreffe');
                        xmlwriter_text($xw, $apis['code_greffe']);
                        xmlwriter_end_element($xw);

                        xmlwriter_start_element($xw, 'numeroGestion');
                            xmlwriter_start_element($xw, 'Millesime');
                            xmlwriter_text($xw, $apis['numero_gestion']['millesime']);
                            xmlwriter_end_element($xw);

                            xmlwriter_start_element($xw, 'Statut');
                            xmlwriter_text($xw, $apis['numero_gestion']['statut']);
                            xmlwriter_end_element($xw);

                            xmlwriter_start_element($xw, 'Chrono');
                            xmlwriter_text($xw,  $this->chrono_traite($apis['numero_gestion']['chrono']));
                            xmlwriter_end_element($xw);
                        xmlwriter_end_element($xw);

                        xmlwriter_start_element($xw, 'formeJuridique');
                            xmlwriter_start_element($xw, 'code');
                            xmlwriter_text($xw, $apis['forme_juridique']);
                            xmlwriter_end_element($xw);

                            xmlwriter_start_element($xw, 'libelle');
                            xmlwriter_text($xw, $apis['libelle_forme_juridique']);
                            xmlwriter_end_element($xw);
                        xmlwriter_end_element($xw);

                        xmlwriter_start_element($xw, 'nbBE');
                        xmlwriter_text($xw, $nb_BE);
                        xmlwriter_end_element($xw);

                        xmlwriter_start_element($xw, 'adresse');

                            if ($infos_xml[0][0]['adresse_sociale'] != null) {
                                xmlwriter_start_element($xw, 'ligne2');
                                xmlwriter_text($xw, $infos_xml[0][0]['adresse_sociale']);
                                xmlwriter_end_element($xw);
                            }

                            if ($infos_xml[0][0]['commune_sociale'] != null) {
                                xmlwriter_start_element($xw, 'localite');
                                xmlwriter_text($xw, $infos_xml[0][0]['commune_sociale']);
                                xmlwriter_end_element($xw);
                            }

                            if ($infos_xml[0][0]['code_postal_sociale'] != null) {
                                xmlwriter_start_element($xw, 'codePostal');
                                xmlwriter_text($xw, $infos_xml[0][0]['code_postal_sociale']);
                                xmlwriter_end_element($xw);
                            }

                            if ($infos_xml[0][0]['commune_sociale'] != null) {
                                xmlwriter_start_element($xw, 'bureauDistributeur');
                                xmlwriter_text($xw, $infos_xml[0][0]['commune_sociale']);
                                xmlwriter_end_element($xw);
                            }

                            if (isset($apis['adresse']['code_insee'])) {
                                xmlwriter_start_element($xw, 'codeCommuneINSEE');
                                xmlwriter_text($xw, $apis['adresse']['code_insee']);
                                xmlwriter_end_element($xw);
                            }

                            if ($infos_xml[0][0]['pays_sociale'] != null) {
                                xmlwriter_start_element($xw, 'pays');
                                xmlwriter_text($xw, $infos_xml[0][0]['pays_sociale']);
                                xmlwriter_end_element($xw);
                            }
                        xmlwriter_end_element($xw);
                    xmlwriter_end_element($xw); // 'identificationEntreprise'

                    xmlwriter_start_element($xw, 'infoSaisie');
                        xmlwriter_start_element($xw, 'dateSaisie');
                        xmlwriter_text($xw, $date_saisie);
                        xmlwriter_end_element($xw);

                        xmlwriter_start_element($xw, 'codeRejet');
                        xmlwriter_text($xw, $infos_xml[0][0]['code_rejet']);
                        xmlwriter_end_element($xw);

                        xmlwriter_start_element($xw, 'identificationDepot');
                            xmlwriter_start_element($xw, 'dateDepot');
                            xmlwriter_text($xw, $apis['depot']['date']);
                            xmlwriter_end_element($xw);

                            xmlwriter_start_element($xw, 'numeroDepot');
                            xmlwriter_text($xw, $apis['depot']['numero']);
                            xmlwriter_end_element($xw);

                            xmlwriter_start_element($xw, 'acte');
                                if (isset($apis['depot']['acte']['date'])) {
                                    xmlwriter_start_element($xw, 'dateActe');
                                    xmlwriter_text($xw, $apis['depot']['acte']['date']);
                                    xmlwriter_end_element($xw);
                                }

                                if (isset($apis['depot']['acte']['numero'])) {
                                    xmlwriter_start_element($xw, 'numeroActe');
                                    xmlwriter_text($xw, $apis['depot']['acte']['numero']);
                                    xmlwriter_end_element($xw);
                                }

                                if (isset($apis['depot']['acte']['type'])) {
                                    xmlwriter_start_element($xw, 'typeActe');
                                    xmlwriter_text($xw, $apis['depot']['acte']['type']);
                                    xmlwriter_end_element($xw);
                                }

                                /*
                                xmlwriter_start_element($xw, 'libelleActe');
                                xmlwriter_text($xw, 'XXXXX');
                                xmlwriter_end_element($xw);

                                xmlwriter_start_element($xw, 'decisionActe');
                                xmlwriter_text($xw, 'XXXXX');
                                xmlwriter_end_element($xw);
                                */
                            xmlwriter_end_element($xw);
                        xmlwriter_end_element($xw); // 'identificationDepot'
                    xmlwriter_end_element($xw); // 'infoSaisie'

                    for ($i = 0; $i < $nb_BE; $i++) {
                        xmlwriter_start_element($xw, 'BEffectif');
                            if ($infos_xml[$i][$i]['civilite'] != null) {
                                xmlwriter_start_element($xw, 'Civilite');
                                xmlwriter_text($xw, $infos_xml[$i][$i]['civilite']);
                                xmlwriter_end_element($xw);
                            }

                            xmlwriter_start_element($xw, 'nomPatronymique');
                            xmlwriter_text($xw, $infos_xml[$i][$i]['nom_naissance']);
                            xmlwriter_end_element($xw);

                            if ($infos_xml[$i][$i]['nom_usage'] != null) {
                                xmlwriter_start_element($xw, 'nomMarital');
                                xmlwriter_text($xw, $infos_xml[$i][$i]['nom_usage']);
                                xmlwriter_end_element($xw);
                            }

                            if ($infos_xml[$i][$i]['pseudonyme'] != null) {
                                xmlwriter_start_element($xw, 'Pseudonyme');
                                xmlwriter_text($xw, $infos_xml[$i][$i]['pseudonyme']);
                                xmlwriter_end_element($xw);
                            }

                            if ($infos_xml[$i][$i]['prenom_principal'] != null) {
                                xmlwriter_start_element($xw, 'Prenoms');
                                xmlwriter_text($xw, $infos_xml[$i][$i]['prenom_principal']);
                                xmlwriter_end_element($xw);
                            }

                            xmlwriter_start_element($xw, 'infoNaissance');
                                if ($infos_xml[$i][$i]['naissance_date'] != null) {
                                    xmlwriter_start_element($xw, 'dateNaissance');
                                    xmlwriter_text($xw, $infos_xml[$i][$i]['naissance_date']);
                                    xmlwriter_end_element($xw);
                                }

                                if ($infos_xml[$i][$i]['naissance_lieu'] != null) {
                                    xmlwriter_start_element($xw, 'villeNaissance');
                                    xmlwriter_text($xw, $infos_xml[$i][$i]['naissance_lieu']);
                                    xmlwriter_end_element($xw);
                                }

                                if (isset($apis['beneficiaires'][$i]['lieu_naissance']['code_insee'])) {
                                    xmlwriter_start_element($xw, 'codeCommuneINSEE');
                                    xmlwriter_text($xw, $apis['beneficiaires'][$i]['lieu_naissance']['code_insee']);
                                    xmlwriter_end_element($xw);
                                }

                                if (isset($apis['beneficiaires'][$i]['lieu_naissance']['departement'])) {
                                    xmlwriter_start_element($xw, 'DepartementNaissance');
                                    xmlwriter_text($xw, $apis['beneficiaires'][$i]['lieu_naissance']['departement']);
                                    xmlwriter_end_element($xw);
                                }

                                if ($infos_xml[$i][$i]['departement_pays'] != null) {
                                    xmlwriter_start_element($xw, 'paysNaissance');
                                    xmlwriter_text($xw, $infos_xml[$i][$i]['departement_pays']);
                                    xmlwriter_end_element($xw);
                                }

                                if ($infos_xml[$i][$i]['nationalite'] != null) {
                                    xmlwriter_start_element($xw, 'Nationalite');
                                    xmlwriter_text($xw, $infos_xml[$i][$i]['nationalite']);
                                    xmlwriter_end_element($xw);
                                }
                            xmlwriter_end_element($xw);

                            xmlwriter_start_element($xw, 'adresse');
                                if ($infos_xml[$i][$i]['adresse_domicile'] != null) {
                                    xmlwriter_start_element($xw, 'ligne2');
                                    xmlwriter_text($xw, $infos_xml[$i][$i]['adresse_domicile']);
                                    xmlwriter_end_element($xw);
                                }

                                if ($infos_xml[$i][$i]['commune_domicile'] != null) {
                                    xmlwriter_start_element($xw, 'localite');
                                    xmlwriter_text($xw, $infos_xml[$i][$i]['commune_domicile']);
                                    xmlwriter_end_element($xw);
                                }

                                if ($infos_xml[$i][$i]['code_postal_domicile'] != null) {
                                    xmlwriter_start_element($xw, 'codePostal');
                                    xmlwriter_text($xw, $infos_xml[$i][$i]['code_postal_domicile']);
                                    xmlwriter_end_element($xw);
                                }

                                if (isset($apis['beneficiaires'][$i]['adresse']['code_insee'])) {
                                    xmlwriter_start_element($xw, 'codeCommuneINSEE');
                                    xmlwriter_text($xw, $apis['beneficiaires'][$i]['adresse']['code_insee']);
                                    xmlwriter_end_element($xw);
                                }

                                if ($infos_xml[$i][$i]['pays_domicile'] != null) {
                                    xmlwriter_start_element($xw, 'pays');
                                    xmlwriter_text($xw, $infos_xml[$i][$i]['pays_domicile']);
                                    xmlwriter_end_element($xw);
                                }
                            xmlwriter_end_element($xw);

                            xmlwriter_start_element($xw, 'modaliteControle');
                                xmlwriter_start_element($xw, 'modaliteSociete');
                                    xmlwriter_start_element($xw, 'detentionCapital');
                                        xmlwriter_start_element($xw, 'type');
                                        xmlwriter_text($xw, $infos_xml[$i][$i]['detention_capital']);
                                        xmlwriter_end_element($xw);

                                        if ($infos_xml[$i][$i]['pourcentage_capital'] != null) {
                                            xmlwriter_start_element($xw, 'pourcentage');
                                            xmlwriter_text($xw, $infos_xml[$i][$i]['pourcentage_capital']);
                                            xmlwriter_end_element($xw);
                                        }
                                    xmlwriter_end_element($xw);

                                    xmlwriter_start_element($xw, 'detentionDroitsVote');
                                        xmlwriter_start_element($xw, 'type');
                                        xmlwriter_text($xw, $infos_xml[$i][$i]['detention_droits']);
                                        xmlwriter_end_element($xw);

                                        if ($infos_xml[$i][$i]['pourcentage_droits'] != null) {
                                            xmlwriter_start_element($xw, 'pourcentage');
                                            xmlwriter_text($xw, $infos_xml[$i][$i]['pourcentage_droits']);
                                            xmlwriter_end_element($xw);
                                        }
                                    xmlwriter_end_element($xw);

                                    if ($infos_xml[$i][$i]['autre_moyen_case'] == 1) {
                                        xmlwriter_start_element($xw, 'autreMoyen');
                                        xmlwriter_text($xw, $infos_xml[$i][$i]['exercice']);
                                        xmlwriter_end_element($xw);
                                    }

                                    if ($infos_xml[$i][$i]['representant'] == 1) {
                                        xmlwriter_start_element($xw, 'representantLegal');
                                        xmlwriter_text($xw, "");
                                        xmlwriter_end_element($xw);
                                    }

                                xmlwriter_end_element($xw);
                            xmlwriter_end_element($xw);

                            if ($infos_xml[$i][$i]['date_effect'] != null) {
                                xmlwriter_start_element($xw, 'dateEffet');
                                xmlwriter_text($xw, $infos_xml[$i][$i]['date_effect']);
                                xmlwriter_end_element($xw);
                            }

                        xmlwriter_end_element($xw); // 'BEffectif'
                    }
                xmlwriter_end_element($xw);
            xmlwriter_end_element($xw);
        xmlwriter_end_document($xw);

        $res_xml = xmlwriter_output_memory($xw);

        return $res_xml;
    }

    /**
     * @Route("/addDBE_S_2/{filename}/{beneficiaires}/{siren}", name="addDBE_S_2page")
     */
    public function addDBE_S_2(Request $request, $filename, $beneficiaires, $siren)
    {
        $apis = $this->data_API($siren);
        $infos_db = $this->data_TEST2($siren);
        $infos_operator = $this->data_Operator($request, $siren);

        for ($i = (count($apis['beneficiaires'])-1); $i < $beneficiaires; $i++) {
            $api_merge = ["$i" => array(
                "civilite" => "",
                "nom_patronymique" => "",
                "nom_marital" => "",
                "prenoms" => "",
                "date_naissance" => "",
                "lieu_naissance" => array(
                    "commune" => "",
                    "code_insee" => "",
                    "departement" => "",
                    "pays" => ""
                ),
                "adresse" => array(
                    "rue" => "",
                    "code_postal" => "",
                    "commune" => "",
                    "code_insee" => "",
                    "pays" => ""
                ),
                "nationalite" => "",
                "modalite_controle" => array(
                    "detention_capital" => array(
                        "type" => "",
                        "pourcentage" => ""
                    ),
                    "detention_votes" => array(
                        "type" => "",
                        "pourcentage" => ""
                    )
                ),
                "date_effet" => ""
            )];
            $apis['beneficiaires'] = array_merge($apis['beneficiaires'], $api_merge);
        }

        // return new Response(var_dump($apis['beneficiaires']));

        return $this->render('compare/compare.html.twig', [
            'filename' => $filename,
            'apis' => $apis,
            'siren' => $siren,
            'infos_db' => $infos_db,
            'infos_operator' => $infos_operator
        ]);
    }

    /**
     * @Route("/addDBE_S_bis/{filename}/{api_length}", name="addDBE_S_bispage")
     */
    /*public function addDBE_S_bis($filename, $api_length)
    {
       
        $api_orig = '{ "1":{"a":"zzzzzzzzzzzz", "b":"zzzzzzzzzzzz", "indirecte":"0"}, "2":{"a":"yyyyyyyyyyyyyyy", "b":"yyyyyyyyyyyyyyy", "indirecte":"1"}, "3":{"joint":"jointjointjoint", "b":"yyyyyyyyyyyyyyy"}}';
        $apis = json_decode($api_orig, true);


        for ($i = (count($apis)-1); $i < $api_length; $i++) {
            $api_merge = ["$i"=>["joint"=>" ", "b"=>" "]];
            $apis = array_merge($apis, $api_merge);
        }
        
        return $this->render('compare/compare.html.twig', [
            'filename' => $filename,
            'apis' => $apis
        ]); 
    }*/

    /**
     * @return array
     */
    private function data_TEST2($siren)
    {
        $em_TEST2 = $this->getDoctrine()->getManager('IFG_TEST2')->getConnection();
        $sql = "SELECT * FROM public.ta_suividem_ass p WHERE p.siren='$siren' and p.codetypeacte='BENh' and p.dtsaisie is not null and p.numdepotgreffe is not null ORDER BY dtdepot DESC ";
        $info_saisie = $em_TEST2->prepare($sql);
        $info_saisie->execute();
        $infos_db = $info_saisie->fetchAll();

        if (empty($infos_db)) {
            return $this->render('error/siren.html.twig', [
                'siren' => $siren
            ]);
        } else {
            return $infos_db[0];
        }
    }

    /**
     * @return array
     */
    private function data_API($siren)
    {
        $api_url_GET = "https://apidata.datainfogreffe.fr:8069/associes/rbe?siren=$siren";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url_GET);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_USERPWD, 'infogreffe:3fn4rg2ff2');

        $res_ch = curl_exec($ch);
        curl_close($ch);
        $apis = json_decode($res_ch, true);

        return $apis;
    }

    /**
     * @return array
     */
    private function data_Operator(Request $request, $siren)
    {
        $operator = new Operator();
        $em_SDPD = $this->getDoctrine()->getManager('default');
        $operators_db = $em_SDPD->getRepository('IFG_SDPD:Operator')->findBySiren($siren);

        $infos = array();

        foreach($operators_db as $value) {
            array_push($infos, array(
                'operator' => $value->getOperator(),
                'dateSaisie' => $value->getDateSaisie()
            ));
        }

        $infos_paginate = $this->get('knp_paginator')->paginate($infos, $request->query->get('page',1),3);

        return $infos_paginate;
    }

    /**
     * @return array
     */
    private function infos_check($nb_BE, $infos=array())
    {
        $len_info = count($infos);
        if ( $len_info < $nb_BE ) {
            for ($i = $len_info; $i < $nb_BE; $i++) {
                array_push($infos, "");
            }
        }
        return $infos;
    }

    /**
     * @return array
     */
    private function detention_capital_check($nb_BE, $detention_capital_directe=array(), $detention_capital_indirecte=array())
    {
        $detention_capital = array();

        for ($i = 0; $i < $nb_BE; $i++) {
            if ($detention_capital_directe[$i] && !$detention_capital_indirecte[$i]) {
                $detention_capital[$i] = 0;
            } else if (!$detention_capital_directe[$i] && $detention_capital_indirecte[$i]) {
                $detention_capital[$i] = 1;
            }
            else if (!$detention_capital_directe[$i] && !$detention_capital_indirecte[$i]) {
                $detention_capital[$i] = 2;
            }
            else if ($detention_capital_directe[$i] && $detention_capital_indirecte[$i]) {
                $detention_capital[$i] = 3;
            }
        }

        return $detention_capital;
    }

    /**
     * @return array
     */
    private function detention_droits_check($nb_BE, $detention_droits_directe=array(), $detention_droits_indirecte=array())
    {
        $detention_droits = array();

        for ($i = 0; $i < $nb_BE; $i++) {
            if ($detention_droits_directe[$i] && !$detention_droits_indirecte[$i]) {
                $detention_droits[$i] = 0;
            } else if (!$detention_droits_directe[$i] && $detention_droits_indirecte[$i]) {
                $detention_droits[$i] = 1;
            }
            else if (!$detention_droits_directe[$i] && !$detention_droits_indirecte[$i]) {
                $detention_droits[$i] = 2;
            }
            else if ($detention_droits_directe[$i] && $detention_droits_indirecte[$i]) {
                $detention_droits[$i] = 3;
            }
        }

        return $detention_droits;
    }

    /**
     * @return integer
     */
    private function typeDeclaration_traite($data) {
        switch($data) {
            case "SOC":
                return 0;
            default:
                return null;
        }
    }

    /**
     * @return integer
     */
    private function siren_traite($siren) {
        $res = sprintf("%09d", $siren);
        return $res;
    }

    /**
     * @return integer
     */
    private function chrono_traite($data) {
        $res = sprintf("%05d", $data);
        return $res;
    }

    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        return md5(uniqid());
    }
}

