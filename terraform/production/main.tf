terraform {
  required_version = ">= 0.13"
  backend "s3" {
    profile        = "zanichelli-ced-terraform"
    bucket         = "terraform-zanichelli-ced"
    key            = "terraform/aws-ced/production/sendy/terraform.tfstate"
    region         = "eu-west-1"
    dynamodb_table = "app-state"
  }

  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 2.13"
    }
  }
}
provider "aws" {
  profile             = var.profile
  region              = var.region
  allowed_account_ids = ["305507912930"] // Optional
}

// fetches the application load balancer
data "aws_lb" "load-balancer" {
  name = "opzioni-alb" // to verify if it makes sense to use the name
}

// fetches the https listener
data "aws_lb_listener" "alb_listener" {
  load_balancer_arn = data.aws_lb.load-balancer.arn
  port              = 443
}

// fetches the vpc
data "aws_vpc" "vpc" {
  filter {
    name   = "tag:Project-id"
    values = ["zanichelli"]
  }
}

data "aws_ami" "image-sendy" {
  owners      = ["305507912930"]
  most_recent = true
  filter {
    name   = "tag:Project"
    values = ["sendy"]
  }
}

data "aws_subnet_ids" "subnet_ids" {
  vpc_id = data.aws_vpc.vpc.id
}

module "instance-sendy" {
  source = "git::ssh://git@bitbucket.org/zanichelli/terraform-instance-frontend.git?ref=v1.0.2"
  #source = "../../../../terraform-instance-frontend"
  project         = "sendy"
  environment     = var.environment
  public_key_file = file("${path.module}/${var.public_key_file}")
  instance_type   = "t3.micro"
  vpc_id          = data.aws_vpc.vpc.id
  subnet_ids      = data.aws_subnet_ids.subnet_ids
  ami_id          = data.aws_ami.image-sendy.id
  volume_size     = "20"
  cost_center_tag = var.cost_center_tag
  owner           = var.owner
}

data "aws_security_group" "redis-security-group-sad" {
  filter {
    name   = "tag:Name"
    values = [var.redis_sg_tag_name]
  }
}

resource "aws_security_group_rule" "frontend-spot-sad" {
  description              = "allow connection to elasticache from sendy"
  type                     = "ingress"
  from_port                = 6379
  to_port                  = 6379
  protocol                 = "tcp"
  security_group_id        = data.aws_security_group.redis-security-group-sad.id
  source_security_group_id = module.instance-sendy.security_group_id
}

module "alb-listener-rule-sendy-https" {
  source = "git::ssh://git@bitbucket.org/zanichelli/terraform-alb-listener-rule.git?ref=v0.0.8"
  //source = "../../../../terraform-alb-listener-rule"
  project                       = "sendy"
  environment                   = var.environment
  listener_arn                  = data.aws_lb_listener.alb_listener.arn
  alb_host_header_values        = ["sendy.zanichelli.it"]
  target_group_name             = "sendy-https-target-group"
  alb_target_port               = "443"
  alb_target_protocol           = "HTTPS"
  vpc_id                        = data.aws_vpc.vpc.id
  target_group_healthcheck_path = "/documentation"
}

#Instance Attachment
resource "aws_alb_target_group_attachment" "sendy-https-frontend" {
  target_group_arn = module.alb-listener-rule-sendy-https.target_group_arn
  target_id        = module.instance-sendy.instance_id
  port             = 443
}

resource "aws_eip" "sendy-eip" {
  instance = module.instance-sendy.instance_id
  vpc      = true
  tags = {
    Name        = "sendy-production"
    Created-by  = "terraform"
    Environment = var.environment
  }
}

data "aws_security_group" "sendy-security-group" {
  filter {
    name   = "tag:Name"
    values = [var.sendy_sg_tag_name]
  }
}
resource "aws_security_group_rule" "frontend-spot" {
  description              = "allow connection to elastic cache from sendy"
  type                     = "ingress"
  from_port                = 6379
  to_port                  = 6379
  protocol                 = "tcp"
  security_group_id        = data.aws_security_group.redis-security-group-sad.id
  source_security_group_id = data.aws_security_group.sendy-security-group.id
}


output "sendy_public_ip_address" {
  description = "The ip address of sendy machine."
  value       = aws_eip.sendy-eip.public_ip
}
