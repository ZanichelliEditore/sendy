/*
A template file, provided in the templates directory is parsed and its variables are filled
*/
data "template_file" "dev_hosts" {
  depends_on = [var.instance_public_ip]
  template   = file("${path.module}/templates/ansible.inv.template")
  vars = {
    project       = var.project
    port          = var.port
    public_ip     = var.instance_public_ip
    environment   = var.environment
  }
}
resource "aws_s3_bucket_object" "inventory-file" {
  bucket                 = var.bucket_inventory
  key                    = "${var.project}-inventory"
  content                = data.template_file.dev_hosts.rendered
  server_side_encryption = "AES256"
}