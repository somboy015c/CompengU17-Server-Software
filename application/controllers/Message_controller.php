<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Message_controller extends Home_Core_Controller
{
	public function __construct()
	{
		parent::__construct();
		//check user
		if (!auth_check()) {
			redirect(lang_base_url());
		}
	}

	/**
	 * Messages
	 */
	public function messages()
	{
		$data['title'] = trans("messages");
		$data['description'] = trans("messages") . " - " . $this->app_name;
		$data['keywords'] = trans("messages") . "," . $this->app_name;

		$data['conversation'] = $this->message_model->get_user_latest_conversation($this->auth_user->id);
		

		if (!empty($data['conversation'])) {
			$data['unread_conversations'] = $this->message_model->get_unread_conversations($this->auth_user->id);
			$data['read_conversations'] = $this->message_model->get_read_conversations($this->auth_user->id);
			$data['messages'] = $this->message_model->get_messages($data['conversation']->id);
			$this->message_model->set_conversation_messages_as_read($data['conversation']->id);
		}

		$this->load->view('partials/_header', $data);
		$this->load->view('message/messages', $data);
		$this->load->view('partials/_footer');
	}

	/**
	 * Conversation
	 */
	public function conversation($id)
	{
		$data['title'] = trans("messages");
		$data['description'] = trans("messages") . " - " . $this->app_name;
		$data['keywords'] = trans("messages") . "," . $this->app_name;

		$data['conversation'] = $this->message_model->get_conversation($id);
		
		//check message
		if (empty($data['conversation'])) {
			redirect(lang_base_url() . "messages");
		}
		//check message owner
		if ($this->auth_user->id != $data['conversation']->sender_id && $this->auth_user->id != $data['conversation']->receiver_id) {
			redirect(lang_base_url() . "messages");
		}
		$data['unread_conversations'] = $this->message_model->get_unread_conversations($this->auth_user->id);
		$data['read_conversations'] = $this->message_model->get_read_conversations($this->auth_user->id);
		$data['messages'] = $this->message_model->get_messages($data['conversation']->id);
		$this->message_model->set_conversation_messages_as_read($data['conversation']->id);

		$this->load->view('partials/_header', $data);
		$this->load->view('message/messages', $data);
		$this->load->view('partials/_footer');
	}

	/**
	 * Send Message
	 */
	public function send_message()
	{
		$conversation_id = $this->input->post('conversation_id', true);
		if ($this->message_model->add_message($conversation_id)) {
			$conversation = $this->message_model->get_conversation($conversation_id);
			if (!empty($conversation)) {
				//send email
				$sender_id = $this->input->post('sender_id', true);
				$receiver_id = $this->input->post('receiver_id', true);
				$message = $this->input->post('message', true);
				$user = get_user($receiver_id);

				if (!empty($user) && $user->send_email_new_message == 1 && !empty($message)) {
					$email_data = array(
						'email_type' => 'new_message',
						'sender_id' => $sender_id,
						'receiver_id' => $receiver_id,
						'message_subject' => $conversation->subject,
						'message_text' => $message
					);
					$this->session->set_userdata('mds_send_email_data', json_encode($email_data));
				}
			}
		}
		redirect($this->agent->referrer());
	}

	/**
	 * Add Conversation
	 */
	public function add_conversation()
	{
		if ($this->auth_user->id == $this->input->post('receiver_id', true)) {
			$this->session->set_flashdata('error', trans("msg_message_sent_error"));
			$this->load->view('partials/_messages');
			reset_flash_data();
			if ($this->input->post('redirect', true)) {
				$this->session->set_flashdata('error', trans("msg_error"));
				redirect($this->agent->referrer());
			}
		} else {
			$conversation_id = $this->message_model->add_conversation();
			if ($conversation_id) {
				if ($this->message_model->add_message($conversation_id)) {
					$this->session->set_flashdata('success', trans("msg_message_sent"));
					$this->load->view('partials/_messages');
					reset_flash_data();
					if ($this->input->post('redirect', true)) {
						$this->session->set_flashdata('success', trans("msg_message_sent"));
						redirect($this->agent->referrer());
					}
				} else {
					$this->session->set_flashdata('error', trans("msg_error"));
					$this->load->view('partials/_messages');
					reset_flash_data();
					if ($this->input->post('redirect', true)) {
						$this->session->set_flashdata('error', trans("msg_error"));
						redirect($this->agent->referrer());
					}
				}
			} else {
				$this->session->set_flashdata('error', trans("msg_error"));
				$this->load->view('partials/_messages');
				reset_flash_data();
				if ($this->input->post('redirect', true)) {
					$this->session->set_flashdata('error', trans("msg_error"));
					redirect($this->agent->referrer());
				}
			}
		}
	}

	/**
	 * Delete Conversation
	 */
	public function delete_conversation()
	{
		$conversation_id = $this->input->post('conversation_id', true);
		$this->message_model->delete_conversation($conversation_id);
	}

}
