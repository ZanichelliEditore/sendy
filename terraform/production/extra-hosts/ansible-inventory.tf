/*
Aws instance
*/
data "aws_instances" "running-instances" {
  instance_tags = {
    Created-by = "terraform"
  }
  instance_state_names = ["running"]
  depends_on           = [var.instance]
}
/*
A template file, provided in the templates directory is parsed and its variables are filled
*/
data "template_file" "dev_hosts" {
  template = file("${path.module}/templates/ansible.inv.template")
  vars = {
    environment = var.environment
    public_ips  = join(",", data.aws_instances.running-instances.public_ips)
  }
}

resource "aws_s3_bucket_object" "inventory-file" {
  bucket                 = var.bucket_inventory
  key                    = "ansible-inventory"
  content                = data.template_file.dev_hosts.rendered
  server_side_encryption = "AES256"
}
