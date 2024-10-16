FROM ubuntu:22.04
ARG USER
ARG UID
ARG GID

ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update && apt-get install -y software-properties-common locales

RUN locale-gen en_US.UTF-8
ENV LANG=en_US.UTF-8

RUN apt-get update && \
    apt-get install -y --force-yes \
	    nano \
	    git \
	    curl \
        zsh

RUN apt-add-repository -y ppa:ondrej/php && apt-get update && apt-get install -y --force-yes \
	php8.2-cli \
	php8.2-curl \
	php8.2-intl \
	php8.2-mysql \
	php8.2-xml \
	php8.2-mbstring \
	php8.2-bcmath \
	php8.2-zip \
	php8.2-opcache \
	php8.2-bz2 \
	php8.2-gmp \
	php8.2-sqlite \
	php8.2-pgsql \
    php8.2-xdebug \
    php8.2-redis

RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer

RUN groupadd -g ${GID} -r ${USER} && useradd -u ${UID} -rm -s /bin/zsh -g ${USER} -G audio,video ${USER} \
    && mkdir -p /home/${USER}/Downloads \
    && chown -R ${USER}:${USER} /home/${USER}

RUN apt install wget && wget https://get.symfony.com/cli/installer -O - | bash
RUN mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

RUN wget https://cs.symfony.com/download/php-cs-fixer-v2.phar -O php-cs-fixer \
    && chmod a+x php-cs-fixer \
    && mv php-cs-fixer /usr/local/bin/php-cs-fixer

WORKDIR /home/${USER}

USER ${USER}

RUN git clone https://github.com/ohmyzsh/ohmyzsh.git /home/${USER}/.oh-my-zsh \
      && cp /home/${USER}/.oh-my-zsh/templates/zshrc.zsh-template /home/${USER}/.zshrc \
      && sed -i.bak 's/robbyrussell/nebirhos/' /home/${USER}/.zshrc

RUN echo "plugins=(git yarn)" >> /home/${USER}/.zshrc

# from https://hub.docker.com/r/themattrix/develop/~/dockerfile/
RUN git clone https://github.com/junegunn/fzf.git /home/${USER}/.fzf \
    && (cd /home/${USER}/.fzf) \
    && echo "export FZF_DEFAULT_OPTS='--no-height --no-reverse'" >> /home/${USER}/.zshrc

RUN mkdir /home/${USER}/current
RUN touch /home/${USER}/.zsh_history
WORKDIR /home/${USER}/current
