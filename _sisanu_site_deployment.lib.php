<?
class wpmu_deployment
{
	var $wpdb='';					// the global sql operation
	var $blog_id=0;					// the selected site for the import
	var $deployment_steps=array();	// the array of steps to be deployed
	var $step_no=0;					// this is the counter of the steps
	var $step_page=1;
	var $exec_per_step_page=30;
	var $WPMU_TP="[WPMU_TP]";		// this will be replaced with the local table prefix
	var $plugin_action_path='';		// the plugin will put the action in admin at this url
	var $navigation_path='';		// the navigation title and path of the plugin
	var $navigation_path_title='';
	var $navigation_path_link=array();
	var $path_sep=' &raquo; ';		// the separator of the links in the navigation path
	var $display_deployment_content='';		// the navigation title and path of the plugin
	var $blog_prefix='';
	var $selected_receiver='';
	var $list_receivers='';
	var $your_code='';
	var $site_path='';

	function __construct()
	{
		global $wpdb,$sisanu_site_deployment_receivers;
		$this->wpdb=$wpdb;
		$this->blog_prefix=$this->wpdb->get_blog_prefix(0);
		$list_types=array();
		$this->list_receivers=$sisanu_site_deployment_receivers;
		$this->selected_receiver=(!empty($_SESSION[$this->blog_prefix.'sisanu_site_deployment_receiver'])) ? $_SESSION[$this->blog_prefix.'sisanu_site_deployment_receiver']:'';
		$this->your_code=md5($this->blog_prefix.DB_NAME.date('Y-m').ABSPATH);
	}
	//----------------------------------------------------------------------------
	function reset_deployment()
	{
		@session_unregister($this->blog_prefix.'sisanu_site_deployment');
		@session_unregister($this->blog_prefix.'sisanu_site_deployment_receiver');
		$this->selected_receiver='';
	}
	function display_navigation_path()
	{
		$this->navigation_path='<div class="deployment_nav"><h2>'.$this->navigation_path_title.'</h2>';
		$list='';
		foreach($this->navigation_path_link as $k=>$v)
		{
			$list.=($list=='')?'You are here :: ':$this->path_sep;
			if(!empty($v[1]))
			{
				$list.='<a href="'.$v[1].'">'.$v[0].'</a>';
			}
			else
			{
				$list.=$v[0];
			}
		}
		$this->navigation_path.=$list.'</div>';
	}
	function display_deployment_selector()
	{
		$this->navigation_path_title='Deployment';
		$this->navigation_path_link[]=array('Deployment');

		if(!empty($_POST["selected_receiver"]))
		{
			if(!session_is_registered($this->blog_prefix.'sisanu_site_deployment_receiver'))
			{
				@session_register($this->blog_prefix.'sisanu_site_deployment_receiver');
			}
			$_SESSION[$this->blog_prefix.'sisanu_site_deployment_receiver']=$_POST["selected_receiver"];
			$this->selected_receiver=$_SESSION[$this->blog_prefix.'sisanu_site_deployment_receiver'];

			$this->display_deployment_content.='<form name="sd_sea_frm" id="sd_sea_frm" method="post" action="'.$this->plugin_action_path.'"><input type="hidden" name="page" id="page" value="sisanu-site-deployment-id" /></form><script type="text/javascript">document.getElementById(\'sd_sea_frm\').submit();</script>';
		}
		else
		{
			$this->display_deployment_content.='<br />Here is the list of possible location where you can deploy a selected site. Please make sure you have installed the same plugin and the same version on that application. The receivers ';
			$this->display_deployment_content.='<form name="sd_sea_frm" id="sd_sea_frm" method="post" action="'.$this->plugin_action_path.'"><input type="hidden" name="page" id="page" value="sisanu-site-deployment-id" />';
			$this->display_deployment_content.='<select name="selected_receiver" id="selected_receiver">';
			foreach($this->list_receivers as $rec)
			{
				$this->display_deployment_content.='<option value="'.$rec.'">'.$rec.'</option>';
			}
			$this->display_deployment_content.='</select>';
			$this->display_deployment_content.='&nbsp;<input type="submit" value="Proceed" class="abutton" /></form>';
		}
	}
	function display_current_receiver()
	{
		if(!empty($this->selected_receiver))
		{
			return('<div class="current_receiver">Your deployment will be made at <b>'.$this->selected_receiver.'</b><br /><br /><center><a href="'.$this->plugin_action_path.'&reset_receiver=on" class="abutton">Reset receiver</a></center><br />Your application code is <b>'.$this->your_code.'</b><br /><br />This code has to be confirmed in the list of senders of the application where you make the deployment to. Please note that this code will be changed every month, so you have to resend the code to the webmasters of the other applications whenever the deployment is not available anymore.</div>');
		}
	}
	function display_deployment_page()
	{
		if(!empty($this->blog_id))//the selected blog to be exported-----------------------------------------------------
		{
			$options = $this->wpdb->get_results("SELECT * FROM {$this->blog_prefix}blogs where blog_id='".$this->blog_id."'");
			foreach($options as $site)
			{
				$site_url=stripslashes($site->domain.$site->path);

				$this->site_path=$site->path;

				$this->navigation_path_title='Selected site '.$site->path;
				$this->navigation_path_link[]=array('Deployment',$this->plugin_action_path);
				$this->navigation_path_link[]=array('Selected site '.$site->path);
				$site_name='<font class="font_off">'.stripslashes($site->domain).'</font><font class="font_on">'.stripslashes($site->path).'</font>';
				
				$this->display_deployment_content.='<br />
				<a href="http://'.$site_url.'" target="_blank">Preview</a>, 
				<a href="http://'.$site_url.'wp-admin/ms-sites.php?action=editblog&id='.$site->blog_id.'" target="_blank">Edit</a>,
				<a href="http://'.$site_url.'wp-admin/" target="_blank">Backend</a> '.$site_name.'<br />';

				if(!empty($_REQUEST["deployment_step"]))
				{
					$this->step_page=$_REQUEST["deployment_step"];

					$this->navigation_path_title='Deployment steps page '.$this->step_page;
					$this->navigation_path_link[1]=array('Selected site '.$site->path, $this->plugin_action_path.'&selected_blog='.$this->blog_id);
					$this->navigation_path_link[]=array($this->navigation_path_title);
					
					$this->display_deployment_content.='So you started the deployment... Please be patient and click the "Next Step" button as long as it is required. Remember to use only the button from inside this page and don\'t use the "Back" button from your browser. <em>Note : if the deployment has been interrupted please retake the entire deployment.</em><br />';
					$this->show_steps();
				}
				else
				{
					$this->display_deployment_content.='Take one more look at the details and make sure you want to start the deployment of this selected site. Once you start the deployment there is no turning back... muhahahaha!!!! If there is information with same ID as this website on the deployment server all will be lost.<br />';
					$this->create_steps();
				}
				

			}
		}
		else//the list of blogs that can be selected to be exported------------------------------------------------------------
		{
			$this->navigation_path_title='Deployment';
			$this->navigation_path_link[]=array('Deployment');
						
			$cond="";
			$form='<form name="sd_sea_frm" id="sd_sea_frm" method="get" action="'.$this->plugin_action_path.'"><input type="text" name="sd_text" id="sd_text" /><input type="hidden" name="page" id="page" value="sisanu-site-deployment-id" />&nbsp;<input type="submit" value="search" class="abutton" /></form>';
			if(!empty($_REQUEST["sd_text"]))
			{
				$cond=" where path like '%".addslashes($_REQUEST["sd_text"])."%' ";
				$form='<form name="sd_sea_frm" id="sd_sea_frm" method="get" action="'.$this->plugin_action_path.'"><input type="text" name="sd_text" id="sd_text" value="'.$_REQUEST["sd_text"].'" /><input type="hidden" name="page" id="page" value="sisanu-site-deployment-id" />&nbsp;<input type="submit" value="search" class="abutton" />&nbsp;<input type="button" value="reset" class="abutton" onclick="document.getElementById(\'sd_text\').value=\'\'; document.getElementById(\'sd_sea_frm\').submit()" /></form>';
			}

			$this->display_deployment_content.='<br />Here is the list of sites created by the users of this application. If you want to publish to the live application one of the sites created here you can click the "Export" link near each site. You will be taken to the page where you can see the details of the deployment.<br /><br />You can minimize the list of websites if you '.$form.' the entire list.<br /><br />';
			$pagenum = (!empty($_REQUEST['paged']))?absint($_REQUEST['paged']):1;
			$per_page=20;
			$query="SELECT * FROM {$this->blog_prefix}blogs ".$cond." order by domain, path ";
			$total=$this->wpdb->get_var( str_replace( 'SELECT *', 'SELECT COUNT(blog_id)', $query ) );
			$query.=" LIMIT " . intval( ( $pagenum - 1 ) * $per_page ) . ", " . intval( $per_page );
			$options= $this->wpdb->get_results( $query, ARRAY_A );
			$num_pages = ceil($total / $per_page);
			$page_links = paginate_links( array(
				'base' => add_query_arg( 'paged', '%#%' ),
				'format' => '',
				'prev_text' => __( '&laquo;' ),
				'next_text' => __( '&raquo;' ),
				'total' => $num_pages,
				'current' => $pagenum
			));
			if($page_links)
			{
				$this->display_deployment_content.='<div class="tablenav"><div class="tablenav-pages">';
				$page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s' ) . '</span>%s',
				number_format_i18n( ( $pagenum - 1 ) * $per_page + 1 ),
				number_format_i18n( min( $pagenum * $per_page, $total ) ),
				number_format_i18n( $total ),
				$page_links
				); 
				$this->display_deployment_content.=$page_links_text.'</div></div>';	
			}
			$this->display_deployment_content.='<ol start="'.((($pagenum-1)*$per_page)+1).'">';
			foreach($options as $site)
			{
				$site_url=stripslashes($site["domain"].$site["path"]);
				$site_name='<font class="font_off">'.stripslashes($site["domain"]).'</font><font class="font_on">'.stripslashes($site["path"]).'</font>';
				$this->display_deployment_content.='<li>
				<a href="http://'.$site_url.'" target="_blank">Preview</a>, 
				<a href="http://'.$site_url.'wp-admin/ms-sites.php?action=editblog&id='.$site["blog_id"].'" target="_blank">Edit</a>, 
				<a href="http://'.$site_url.'wp-admin/" target="_blank">Backend</a>,
				<a href="'.$this->plugin_action_path.'&selected_blog='.$site["blog_id"].'" class="abutton">Export</a> '.$site_name.'</li>';
			}
			$this->display_deployment_content.='</ol>';			
		}
	}
	//----------------------------------------------------------------------------
	function myenc($x)
	{
		$x=str_replace('=','a778899c',$x);
		$x=str_replace('+','a112233b',$x);
		$x=str_replace('/','a445566b',$x);
		return($x);
	}
	function mydec($x)
	{
		$x=str_replace('a778899c','=',$x);
		$x=str_replace('a112233b','+',$x);
		$x=str_replace('a445566b','/',$x);
		return($x);
	}
	//----------------------------------------------------------------------------
	function create_steps()
	{
		#step structure 
		$steps=Array();
		if(!empty($this->blog_id))
		{
			$steps[$this->step_no]["type"]="query";
			$steps[$this->step_no]["name"]="Delete the site from (*)_blogs table";
			$steps[$this->step_no]["content"]="DELETE FROM ".$this->WPMU_TP."blogs where blog_id='".$this->blog_id."' ";
			$this->step_no++;
			for($i=0;$i<=$this->exec_per_step_page;$i++)
			{
				$steps[$this->step_no]["type"]="query";
				$steps[$this->step_no]["name"]="Checking the (*)_blogs table";
				$steps[$this->step_no]["content"]="select id from ".$this->WPMU_TP."blogs";
				$this->step_no++;
			}
			$site = $this->wpdb->get_results("SELECT * FROM {$this->blog_prefix}blogs where blog_id='".$this->blog_id."'",ARRAY_A);
			$steps[$this->step_no]["type"]="query";
			$steps[$this->step_no]["name"]="Insert the site in (*)_blogs table";
			$steps[$this->step_no]["content"]="INSERT INTO ".$this->WPMU_TP."blogs set ";
			$list='';
			foreach($site[0] as $k=>$v)
			{
				$list.=($list=='')?'':', ';
				$list.=$k."='".$v."'";
			}
			$steps[$this->step_no]["content"].=$list." ;";
			$this->step_no++;
			for($i=0;$i<=$this->exec_per_step_page;$i++)
			{
				$steps[$this->step_no]["type"]="query";
				$steps[$this->step_no]["name"]="Checking the (*)_blogs table";
				$steps[$this->step_no]["content"]="select id from ".$this->WPMU_TP."blogs";
				$this->step_no++;
			}
			//------------------------------------------------------------------
			$reg = $this->wpdb->get_results("SELECT * FROM {$this->blog_prefix}registration_log where blog_id='".$this->blog_id."'",ARRAY_A);
			foreach($reg as $kr=>$vr)
			{
				$steps[$this->step_no]["type"]="query";
				$steps[$this->step_no]["name"]="Insert into (*)_registration_log table";
				$steps[$this->step_no]["content"]="INSERT INTO ".$this->WPMU_TP."registration_log set ";
				$list='';
				foreach($reg[$kr] as $k=>$v)
				{
					$list.=($list=='')?'':', ';
					$list.=$k."='".$v."'";
				}
				$steps[$this->step_no]["content"].=$list." ;";
				$this->step_no++;
			}

			$reg = $this->wpdb->get_results("SELECT * FROM {$this->blog_prefix}signups where path like '".$this->site_path."'",ARRAY_A);
			foreach($reg as $kr=>$vr)
			{
				$steps[$this->step_no]["type"]="query";
				$steps[$this->step_no]["name"]="Insert into (*)_signups table";
				$steps[$this->step_no]["content"]="INSERT INTO ".$this->WPMU_TP."signups set ";
				$list='';
				foreach($reg[$kr] as $k=>$v)
				{
					$list.=($list=='')?'':', ';
					$list.=$k."='".$v."'";
				}
				$steps[$this->step_no]["content"].=$list." ;";
				$this->step_no++;
			}

			$reg = $this->wpdb->get_results("SELECT * FROM {$this->blog_prefix}usermeta where meta_key like '".str_replace('_','\_',$this->blog_prefix).$this->blog_id."\_%'",ARRAY_A);
			foreach($reg as $kr=>$vr)
			{
				$steps[$this->step_no]["type"]="query";
				$steps[$this->step_no]["name"]="Insert into (*)_usermeta table";
				$steps[$this->step_no]["content"]="INSERT INTO ".$this->WPMU_TP."usermeta set ";
				$list='';
				foreach($reg[$kr] as $k=>$v)
				{
					$list.=($list=='')?'':', ';
					$list.=$k."='".$v."'";
				}
				$steps[$this->step_no]["content"].=$list." ;";
				$this->step_no++;
			}

			$reg2 = $this->wpdb->get_results("SELECT distinct(user_id) as user_id FROM {$this->blog_prefix}usermeta where meta_key like '".str_replace('_','\_',$this->blog_prefix).$this->blog_id."\_%'",ARRAY_A);
			foreach($reg2 as $kr2=>$vr2)
			{
				$reg = $this->wpdb->get_results("SELECT * FROM {$this->blog_prefix}users where ID='".$vr2["user_id"]."'",ARRAY_A);
				foreach($reg as $kr=>$vr)
				{
					$steps[$this->step_no]["type"]="query";
					$steps[$this->step_no]["name"]="Insert into (*)_users table";
					$steps[$this->step_no]["content"]="INSERT INTO ".$this->WPMU_TP."users set ";

					$list='';
					foreach($reg[$kr] as $k=>$v)
					{
						$list.=($list=='')?'':', ';
						$list.=$k."='".$v."'";
					}
					$steps[$this->step_no]["content"].=$list." ;";
					$this->step_no++;
				}
			}			
			//------------------------------------------------------------------
						
			$tables = $this->wpdb->get_results("SHOW TABLES from ".DB_NAME."",ARRAY_A);
			$test='_'.$this->blog_id.'_';
			foreach($tables as $k=>$v)
			{	
				if(substr_count($v["Tables_in_".DB_NAME],$test)==1)
				{
					$table_name=substr($v["Tables_in_".DB_NAME],strlen($this->blog_prefix));
					if(substr_count($table_name,'comment')==0)
					{
						$steps[$this->step_no]["type"]="query";
						$steps[$this->step_no]["name"]="Delete the (*)_".$table_name." table";
						$steps[$this->step_no]["content"]="DROP TABLE ".$this->WPMU_TP.$table_name." ; ";
						$this->step_no++;
					}
				}
			}
			for($i=0;$i<=$this->exec_per_step_page;$i++)
			{
				$steps[$this->step_no]["type"]="query";
				$steps[$this->step_no]["name"]="Checking the (*)_".$table_name." table";
				$steps[$this->step_no]["content"]="select id from ".$this->WPMU_TP."blogs";
				$this->step_no++;
			}
			foreach($tables as $k=>$v)
			{	
				if(substr_count($v["Tables_in_".DB_NAME],$test)==1)
				{
					$table_name=substr($v["Tables_in_".DB_NAME],strlen($this->blog_prefix));
					$aaaa=$this->wpdb->get_results("SHOW CREATE TABLE ".$this->blog_prefix.$table_name."",ARRAY_N);
					$aaaa[0][1]=str_replace($this->blog_prefix.$table_name,$this->WPMU_TP.$table_name, $aaaa[0][1]);
					$steps[$this->step_no]["type"]="query";
					$steps[$this->step_no]["name"]="Create the table (*)_".$table_name;
					$steps[$this->step_no]["content"]=str_replace('`'.$this->WPMU_TP.$table_name.'`',' IF NOT EXISTS `'.$this->WPMU_TP.$table_name.'` ',$aaaa[0][1])." ; ";
					$this->step_no++;
				}
			}
			for($i=0;$i<=$this->exec_per_step_page;$i++)
			{
				$steps[$this->step_no]["type"]="query";
				$steps[$this->step_no]["name"]="Checking the created tables";
				$steps[$this->step_no]["content"]="select id from ".$this->WPMU_TP."blogs";
				$this->step_no++;
			}
			foreach($tables as $k=>$v)
			{
				if(substr_count($v["Tables_in_".DB_NAME],$test)==1)
				{
					$table_name=substr($v["Tables_in_".DB_NAME],strlen($this->blog_prefix));
					$table_content = $this->wpdb->get_results("select * from ".$v["Tables_in_".DB_NAME],ARRAY_A);
					if(count($table_content)!=0)
					{
						foreach($table_content as $kk=>$vv)
						{
							$steps[$this->step_no]["type"]="query";
							$steps[$this->step_no]["name"]="Insert into (*)_".$table_name." table";
							$steps[$this->step_no]["content"]="INSERT into ".$this->WPMU_TP.$table_name." set "; //IGNORE
							$list='';
							foreach($vv as $key=>$val)
							{
								$list.=($list=='')?'':', ';
								$list.=$key."='".addslashes(stripslashes($val))."'";
							}
							$steps[$this->step_no]["content"].=$list." ; ";
							//$steps[$this->step_no]["content"].=$list." ON DUPLICATE KEY UPDATE ".$list." ; "; // use this if you want to keep old records from your table and just update them
							$this->step_no++;
						}
					}					
				}
			}

			//------------------------------------------------------------------
			if(file_exists(ABSPATH.'wp-content/blogs.dir/'.$this->blog_id.'/'))
			{
				$files=$this->listdir(ABSPATH.'wp-content/blogs.dir/'.$this->blog_id);
				if(!empty($files))
				{
					foreach($files as $file)
					{
						$steps[$this->step_no]["type"]="upload";
						$steps[$this->step_no]["name"]="Upload the file <b>".$file."</b>";
						$steps[$this->step_no]["content"]=$file;
						$this->step_no++;
					}
				}
			}
		}		
		if(!session_is_registered($this->blog_prefix.'sisanu_site_deployment'))
		{
			@session_register($this->blog_prefix.'sisanu_site_deployment');
			$_SESSION[$this->blog_prefix.'sisanu_site_deployment']=array();
		}		
		$_SESSION[$this->blog_prefix.'sisanu_site_deployment']=$steps;
		$this->deployment_steps=$steps;
		$this->display_deployment_content.='<br /><br />The deployment has '.$this->step_no.' actions ('.ceil($this->step_no/$this->exec_per_step_page).' steps). Click here to <a href="'.$this->plugin_action_path.'&selected_blog='.$this->blog_id.'&deployment_step=1" class="abutton"><b>START the deployment</b></a>. By clicking the start button you will run the first actions.';
	}
	function show_steps()
	{
		$nr_steps=ceil(count($_SESSION[$this->blog_prefix.'sisanu_site_deployment'])/$this->exec_per_step_page);
		if($this->step_page<=$nr_steps)
		{
			$this->display_deployment_content.='<br /><br />Step <b>'.$this->step_page.'</b> of <b>'.$nr_steps.'</b> | ';
			if($this->step_page==$nr_steps)
			{
				$this->display_deployment_content.='<a href="'.$this->plugin_action_path.'&selected_blog='.$this->blog_id.'&deployment_step='.($this->step_page+1).'" class="abutton"><b>Finalize &raquo;</b></a>';
			}
			else
			{
				$this->display_deployment_content.='<a href="'.$this->plugin_action_path.'&selected_blog='.$this->blog_id.'&deployment_step='.($this->step_page+1).'" class="abutton"><b>Next Step &raquo;</b></a>';
			}
		}

		if($this->step_page>$nr_steps)
		{
			$this->display_deployment_content.='<br /><br />The site has been deployed. Visit it at <a href="'.$this->selected_receiver.$this->site_path.'" target="_blank">'.$this->selected_receiver.$this->site_path.'</a>';
		}

		$start=($this->step_page-1)*$this->exec_per_step_page;
		$this->deployment_steps=array_slice($_SESSION[$this->blog_prefix.'sisanu_site_deployment'] , $start, $this->exec_per_step_page,true);

		$this->display_deployment_content.='<br /><br /><ol start="'.($start+1).'">';
		foreach($this->deployment_steps as $step=>$content)
		{
			$this->display_deployment_content.='<li>'.$content["name"].'';
			$this->display_deployment_content.=' - <div id="place4'.$step.'" style="display:inline"></div></li><script type="text/javascript">makeRequest(\''.$this->plugin_action_path.'&location=sender&step='.$step.'\',\'place4'.$step.'\')</script>';
			$this->display_deployment_content.='<div id="over4'.$step.'" style="display: block; position:fixed; width: 100%; height: 100%; top: 0px; left: 0px; background: #FFFFFF; filter: alpha(opacity=5); -moz-opacity: 0.05; opacity: .05; z-index: 120;"><div style="padding:25%; font-size:3em; text-align:center">Wait, the action is in progress...</div></div>';
		}
		$this->display_deployment_content.='</ol>';
	}
	function send_step($step)
	{
		if(!empty($_SESSION[$this->blog_prefix.'sisanu_site_deployment'][$step]))
		{
			$content=$_SESSION[$this->blog_prefix.'sisanu_site_deployment'][$step];
			$post_result='';
			if($content["type"]=='query')########################################################
			{
				$url1=parse_url(get_option('siteurl'));
				$url2=parse_url($this->selected_receiver);
				$content["content"]=trim(str_replace(chr(13).chr(10),' ',$content["content"]));
				$fc=str_replace($url1["host"],$url2["host"],$content["content"]);
				$file_content=$this->myenc(base64_encode($fc));//256000
				$post_data=array();
				$post_data["FILE_CONTENT"]=$file_content; 
				$post_data["FILE_NAME"]='';
				$post_data["TYPE"]='query';
				$post_data["sender"]=$this->your_code;
				$post_data['action']=$step;
				$result=$this->post_it($post_data,''); 

				if(substr_count($result,'execute=1')!=0 || substr_count($result,'200 OK')!=0 )
				{
					$post_result='<font class="font_on"><b>OK</b> - successfully</font>';
				}
				else
				{
					$post_result='<font style="color:red"><b>Error</b> on step '.($step+1).' : <b>'.$result.'</b></font>';
				}
			}
			elseif($content["type"]=='upload')#####################################################
			{
				$file=$content["content"];//PHYSICAL_PATH.$v;
				$fc=fread(fopen(ABSPATH.$file, "rb"), filesize(ABSPATH.$file));
				$file_content=$this->myenc(base64_encode($fc));//256000
				$post_data=array();
				$post_data["FILE_CONTENT"]=$file_content; 
				$post_data["FILE_NAME"]=$content["content"];
				$post_data["sender"]=$this->your_code;
				$post_data['action']=$step;
				$result=$this->post_it($post_data,$http_path_to); 
				if(substr_count($result,'upload=1')!=0)
				{
					$post_result='<font class="font_on"><b>'.$content["content"].'</b> - uploaded successfully</font>';
				}
				else
				{
					$post_result='<font style="color:red"><b>Error</b> on step '.($step+1).' : <b>'.$content["content"].'</b></font>';
				}
			}
			$post_result.='<script type="text/javascript">document.getElementById(\'over4'.$step.'\').style.display=\'none\'</script>';
			return($post_result);
		}
	}
	//----------------------------------------------------------------------------
	function post_it($array,$uri)
	{
		$uri=$this->selected_receiver.'/wp-admin/admin.php?page=sisanu-site-deployment-id&location=receiver';		
		$url=preg_replace("@^http://@i","",$uri); 
		$host=substr($url,0,strpos($url,"/")); 
		$postdata=""; 
		foreach($array as $key=>$val)
		{ 
			if(!empty($postdata))
			{
				$postdata.="&"; 
			}
			$postdata.=$key."=".urlencode($val); 
		} 
		$da=fsockopen($host,80,$errno,$errstr);
		if(!$da) 
		{
			echo "$errstr ($errno)<br/>\n";
			echo $da;
		}
		else 
		{
			$salida ="POST $uri  HTTP/1.1\r\n";
			$salida.="Host: $host\r\n";
			$salida.="User-Agent: PostIt\r\n";
			$salida.="Content-Type: application/x-www-form-urlencoded\r\n";
			$salida.="Content-Length: ".strlen($postdata)."\r\n";
			$salida.="Connection: close\r\n\r\n";
			$salida.=$postdata;
			fwrite($da, $salida);
			while(!feof($da))
			{
				$response.=fgets($da,128);
			}
			$response=split("\r\n\r\n",$response);
			$header=$response[0];
			$responsecontent=$response[1];
			if(!(@strpos($header,"Transfer-Encoding: chunked")===false))
			{
				$aux=split("\r\n",$responsecontent);
				for($i=0;$i<count($aux);$i++)
				{
					if($i==0 || ($i%2==0))
					{
						$aux[$i]="";
					}
				}
				$responsecontent=implode("",$aux);
			}
			return chop($responsecontent);
		}
	}
	//----------------------------------------------------------------------------
	function listdir($start_dir)
	{
		$files=array();
		if(is_dir($start_dir))
		{
			$fh=opendir($start_dir);
			while(($file=readdir($fh))!==false) 
			{
				if(strcmp($file,'.')==0 || strcmp($file,'..')==0)
				{
					continue;
				}
				$filepath=$start_dir.'/'.$file;
				if(is_dir($filepath))
				{
					$files=array_merge($files,$this->listdir($filepath));
				}
				else
				{
					array_push($files,str_replace(ABSPATH,'',$filepath));
				}
			}
			closedir($fh);
		} 
		else
		{
			$files=false;
		}
		return $files;
	}
	//----------------------------------------------------------------------------
	function act_like_receiver()
	{
		$global_tmp='/';
		$PAGE_MSG='upload=0';
		if(!empty($_POST))
		{
			foreach ($_POST as $key=>$value )
			{
				if($key=='FILE_CONTENT')
				{
					$file_content=base64_decode($this->mydec($value));
				}
				elseif($key=='FILE_NAME')
				{
					$file_name=$value;
				}
				elseif($key=='TYPE')
				{
					$packet_type=$value;
				}
			}
			
			if(!empty($file_content))
			{
				if(!empty($file_name) && empty($packet_type))
				{
					$text_dir=explode('/',$file_name);
					$dir='';
					for($i=0;$i<count($text_dir)-1;$i++)
					{
						$dir.=($dir!='')?'/':'';
						$dir.=$text_dir[$i];
					}
					if(!is_dir(ABSPATH.$global_tmp.$dir))
					{
						mkdir(ABSPATH.$global_tmp.$dir, 0777, true);
					}
					$f=fopen(ABSPATH.$global_tmp.$file_name, "w+"); 
					fputs($f,$file_content); 
					fclose($f);
					$PAGE_MSG='upload=1';
				}
				
				if(empty($file_name) && !empty($packet_type) && $packet_type=='query')
				{
					$file_content=str_replace($this->WPMU_TP,$this->blog_prefix,$file_content);
					$file_content=trim(str_replace(chr(13).chr(10),' ',$file_content));
					echo '['.$file_content.']<br /><br />';
					$this->wpdb->query($file_content); //to be put on live
					$PAGE_MSG=$file_content.'|execute=1';
					/*
					$test_file=fopen(ABSPATH.$global_tmp."test.txt", "a+"); 
					fputs($test_file, chr(13).chr(10).microtime().' - '.$file_content); 
					fclose($test_file); 
					*/
					//no need to record the stept anymore, the log is not required as the deployment has been tested in the prior version and it works 
				}		
			}			
		}
		echo $PAGE_MSG;
	}
}
?>